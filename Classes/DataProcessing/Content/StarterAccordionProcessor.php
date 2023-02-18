<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\DataProcessing\Content;

use PrototypeIntegration\PrototypeIntegration\Processor\MediaProcessor;
use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use Psr\Log\LoggerInterface;
use StarterTeam\StarterTwig\Processor\BodyTextProcessor;
use StarterTeam\StarterTwig\Processor\HeadlineProcessor;
use TYPO3\CMS\Core\Log\LogManagerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class StarterAccordionProcessor
 */
class StarterAccordionProcessor implements PtiDataProcessor
{
    use AssetTrait;

    /**
     * @var string
     */
    public const ACCORDION_TABLE = 'tx_starter_accordion_element';

    /**
     * @var string
     */
    public const ACCORDION_REFERENCE_FIELD = 'tt_content_accordion';

    /**
     * @var int
     */
    public const ACCORDION_TYPE_TEXT = 1;

    /**
     * @var int
     */
    public const ACCORDION_TYPE_IMAGE = 0;

    protected array $configuration = [];

    protected ContentObjectRenderer $contentObject;

    protected HeadlineProcessor $headlineProcessor;

    protected BodyTextProcessor $bodyTextProcessor;

    protected MediaProcessor $mediaProcessor;

    protected LoggerInterface $logger;

    protected ?array $assetFields = null;

    public function __construct(
        ContentObjectRenderer $contentObjectRenderer,
        HeadlineProcessor $headlineProcessor,
        BodyTextProcessor $bodyTextProcessor,
        MediaProcessor $mediaProcessor,
        LogManagerInterface $logManager
    ) {
        $this->contentObject = $contentObjectRenderer;
        $this->headlineProcessor = $headlineProcessor;
        $this->bodyTextProcessor = $bodyTextProcessor;
        $this->mediaProcessor = $mediaProcessor;
        $this->logger = $logManager->getLogger(self::class);
    }

    public function process(array $data, array $configuration): ?array
    {
        $this->configuration = $configuration;
        $this->setAssetFieldConfiguration();

        $accordionItems = $this->getAccordionItems($data);

        if (count($accordionItems) <= 0) {
            $message = sprintf(
                'Could not render accordion with UID %s too few accordion items (%s), min (%s) items required',
                $data['uid'],
                0,
                1
            );
            $this->logger->warning($message);

            return null;
        }

        return [
            'uid' => $data['uid'],
            'CType' => str_replace('_', '-', $data['CType']),
            'header' => $this->headlineProcessor->processHeadline($data),
            'subheader' => $this->headlineProcessor->processSubLine($data),
            'overline' => $this->headlineProcessor->processOverLine($data),
            'space_before_class' => $data['space_before_class'],
            'space_after_class' => $data['space_after_class'],
            'items' => $this->renderAccordionItems($accordionItems),
            'tx_starter_visibility' => $data['tx_starter_visibility'],
            'tx_starter_background_fluid' => (bool)$data['tx_starter_background_fluid'],
            'tx_starter_container' => $data['tx_starter_width'],
        ];
    }

    /**
     * @deprecated since 4.0.0 and would be remove in 5.0.0, use HeadlineProcessor:class instead
     */
    protected function getHeader(array $data): array
    {
        trigger_error(
            __FUNCTION__ . ' will be removed in EXT:starter-twig v5.0.0, use HeadlineProcessor:class instead.',
            E_USER_DEPRECATED
        );

        return [
            'headline' => $this->headlineProcessor->processHeadline($data),
            'subline' => $this->headlineProcessor->processSubLine($data),
        ];
    }

    protected function renderAccordionItems(array $accordionItems): array
    {
        $accordionData = [];
        foreach ($accordionItems as $accordionItem) {
            try {
                $accordionData[] = $this->translateSliderItem($accordionItem);
            } catch (\Exception $exception) {
                // There was an error rendering the accordionItem. Log the error and ignore the item
                $message = sprintf('Could not render accordion item with UID %s', $accordionItem['uid']);
                $this->logger->warning($message, ['exception' => $exception]);
            }
        }

        return $accordionData;
    }

    /**
     * Convert an accordion item record to the accordion item required by the view.
     */
    protected function translateSliderItem(array $accordionItem): array
    {
        $assets = $this->getAssets($accordionItem);

        return [
            'title' => $accordionItem['header'],
            'bodytext' => $this->bodyTextProcessor->processBodyText($accordionItem),
            'image' => $assets,
            'tx_starter_imageorient' => $this->getImagePosition((int)$accordionItem['imageorient']),
            'grid' => $this->getGrid($accordionItem, $assets),
        ];
    }

    /**
     * Retrieve the accordion items from the db.
     */
    protected function getAccordionItems(array $data): array
    {
        return $this->contentObject->getRecords(
            self::ACCORDION_TABLE,
            [
                'where' => sprintf(self::ACCORDION_REFERENCE_FIELD . ' = %s', $data['uid']),
            ]
        );
    }

    protected function getAssets(array $accordionItem): ?array
    {
        $resultMedia = null;

        if ((int)$accordionItem['type'] === self::ACCORDION_TYPE_TEXT || is_null($this->assetFields)) {
            return null;
        }

        foreach ($this->assetFields as $assetField) {
            if (!isset($this->configuration['imageConfig'][$assetField])) {
                $message = sprintf(
                    "Abort asset rending for accordionItem with UID '%s', required asset configuration for asset '%s' not available.",
                    $accordionItem['uid'],
                    $assetField
                );
                $this->logger->warning($message);

                continue;
            }

            $imageConfig = $this->configuration['imageConfig'][$assetField] ?? [];
            $imagePlaceholderConfig = $this->configuration['imageConfigPlaceholder'][$assetField] ?? [];

            $mediaFileData = $this->mediaProcessor->renderMedia(
                $accordionItem,
                self::ACCORDION_TABLE,
                $assetField,
                $imageConfig,
                $imagePlaceholderConfig
            );

            if (count($mediaFileData) != 1) {
                $message = sprintf(
                    "Invalid number '%s' of images for accordionItem '%s'",
                    count($mediaFileData),
                    $accordionItem['uid']
                );
                $this->logger->warning($message);
                continue;
            }

            if ($mediaFileData[0]['type'] == 'image') {
                $resultMedia[$assetField]['image'] = $mediaFileData[0]['image'];
                $resultMedia[$assetField]['image']['uid'] = $mediaFileData[0]['uid'];
                $resultMedia[$assetField]['image']['placeholder'] =
                    isset($mediaFileData[0]['thumbnail']) ? $mediaFileData[0]['thumbnail']['default'] : null;
            } else {
                $resultMedia[$assetField]['video'] = $mediaFileData[0]['video'];
                $resultMedia[$assetField]['video']['uid'] = $mediaFileData[0]['uid'];
            }
        }

        return $resultMedia;
    }

    protected function setAssetFieldConfiguration(): void
    {
        $assetFieldList = $this->configuration['assetFields'];
        if (!empty($assetFieldList)) {
            $this->assetFields = GeneralUtility::trimExplode(',', $assetFieldList, true);
            foreach ($this->assetFields as $key => $field) {
                if (!isset($GLOBALS['TCA'][self::ACCORDION_TABLE]['columns'][$field])) {
                    unset($this->assetFields[$key]);
                }
            }
        }
    }
}
