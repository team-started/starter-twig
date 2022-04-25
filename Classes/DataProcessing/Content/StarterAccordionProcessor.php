<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\DataProcessing\Content;

use PrototypeIntegration\PrototypeIntegration\Processor\MediaProcessor;
use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
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
    const ACCORDION_TABLE = 'tx_starter_accordion_element';

    /**
     * @var string
     */
    const ACCORDION_REFERENCE_FIELD = 'tt_content_accordion';

    /**
     * @var int
     */
    const ACCORDION_TYPE_TEXT = 1;

    /**
     * @var int
     */
    const ACCORDION_TYPE_IMAGE = 0;

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * @var HeadlineProcessor
     */
    protected $headlineProcessor;

    /**
     * @var BodyTextProcessor
     */
    protected $bodyTextProcessor;

    /**
     * @var MediaProcessor
     */
    protected $mediaProcessor;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var array|null
     */
    protected array $assetFields;

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
        $this->logger = $logManager->getLogger(__CLASS__);
    }

    public function process(array $data, array $configuration): ?array
    {
        $this->configuration = $configuration;
        $this->setAssetFieldConfiguration();

        $accordionItems = $this->getAccordionItems($data);

        if (count($accordionItems) < 0) {
            $message = sprintf(
                'Could not render accordion with UID %s too few accordion items (%s), min (%s) items required',
                $data['uid'],
                count($accordionItems),
                1
            );
            $this->logger->warning($message);

            return null;
        }

        return [
            'uid' => $data['uid'],
            'CType' => str_replace('_', '-', $data['CType']),
            'header' => $this->getHeader($data),
            'space_before_class' => $data['space_before_class'],
            'space_after_class' => $data['space_after_class'],
            'items' => $this->renderAccordionItems($accordionItems),
            'tx_starter_visibility' => $data['tx_starter_visibility'],
            'tx_starter_background_fluid' => (bool) $data['tx_starter_background_fluid'],
        ];
    }

    protected function getHeader(array $data): array
    {
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
     * Convert a accordion item record to the accordion item required by the view.
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

        if ((int)$accordionItem['type'] === self::ACCORDION_TYPE_TEXT) {
            return $resultMedia;
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

            $mediaFileData = $this->mediaProcessor->renderMedia(
                $accordionItem,
                self::ACCORDION_TABLE,
                $assetField,
                $this->configuration['imageConfig'][$assetField]
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
