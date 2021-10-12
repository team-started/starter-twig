<?php
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
    const ACCORDION_TABLE = 'tx_starter_accordion_element';
    const ACCORDION_REFERENCE_FIELD = 'tt_content_accordion';
    const ACCORDION_TYPE_TEXT = 0;
    const ACCORDION_TYPE_IMAGE = 1;

    /**
     * @var array
     */
    protected $configuration;

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

    /**
     * @param array $data
     * @param array $configuration
     * @return array|null
     */
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

        $twigData = [
            'uid' => $data['uid'],
            'header' => $this->getHeader($data),
            'space_before_class' => $data['space_before_class'],
            'space_after_class' => $data['space_after_class'],
            'items' => $this->renderAccordionItems($accordionItems),
        ];

        return $twigData;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getHeader(array $data): array
    {
        $header = [
            'headline' => $this->headlineProcessor->processHeadline($data),
            'subline' => $this->headlineProcessor->processSubLine($data),
        ];

        return $header;
    }

    /**
     *
     * @param array $accordionItems
     * @return array
     */
    protected function renderAccordionItems(array $accordionItems): array
    {
        $accordionData = [];
        foreach ($accordionItems as $accordionItem) {
            try {
                $accordionData[] = $this->translateSliderItem($accordionItem);
            } catch (\Exception $e) {
                // There was an error rendering the accordionItem. Log the error and ignore the item
                $message = sprintf('Could not render accordion item with UID %s', $accordionItem['uid']);
                $this->logger->warning($message, ['exception' => $e]);
            }
        }

        return $accordionData;
    }

    /**
     * Convert a accordion item record to the accordion item required by the view.
     *
     * @param array $accordionItem
     * @return array
     */
    protected function translateSliderItem(array $accordionItem): array
    {
        $assets = $this->getAssets($accordionItem);

        $translatedAccordionItem = [
            'title' => $accordionItem['header'],
            'bodytext' => $this->bodyTextProcessor->processBodyText($accordionItem),
            'image' => $assets,
        ];

        return $translatedAccordionItem;
    }

    /**
     * Retrieve the accordion items from the db.
     *
     * @param array $data
     * @return array
     */
    protected function getAccordionItems(array $data)
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

        if ((int)$accordionItem['type'] !== self::ACCORDION_TYPE_IMAGE) {
            return $resultMedia;
        }

        foreach ($this->assetFields as $assetField) {
            if (!isset($this->configuration['imageConfig'][$assetField])) {
                $message = sprintf(
                    'Abort asset rending for accordionItem with UID \'%s\', required asset configuration for asset \'%s\' not available.',
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
                    'Invalid number \'%s\' of images for accordionItem \'%s\'',
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
