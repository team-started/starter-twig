<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\DataProcessing\Content;

use PrototypeIntegration\PrototypeIntegration\Processor\MediaProcessor;
use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use StarterTeam\StarterTwig\Processor\BodyTextProcessor;
use StarterTeam\StarterTwig\Processor\CtaProcessor;
use StarterTeam\StarterTwig\Processor\HeadlineProcessor;
use TYPO3\CMS\Core\Log\LogManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class StarterCeTextMediaProcessor
 */
class StarterCeTextMediaProcessor implements PtiDataProcessor
{
    /**
     * Image position definition
     *
     * @var array
     */
    const IMAGE_POSITIONS = [
        0 => [
            'x' => 'center',
            'y' => 'above',
            'inside' => true,
        ],
        8 => [
            'x' => 'center',
            'y' => 'below',
            'inside' => true,
        ],
        17 => [
            'x' => 'right',
            'inside' => true,
        ],
        18 => [
            'x' => 'left',
            'inside' => true,
        ],
        25 => [
            'x' => 'right',
            'inside' => false,
        ],
        26 => [
            'x' => 'left',
            'inside' => false,
        ],
    ];

    /**
     * Image crop definition by image position
     * @var array
     */
    const IMAGE_CROP_VARIANT = [
        0 => 'position-above-below',
        8 => 'position-above-below',
        17 => 'position-left-right',
        18 => 'position-left-right',
        25 => 'position-left-right',
        26 => 'position-left-right',
    ];

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
     * @var CtaProcessor
     */
    protected $ctaProcessor;

    /**
     * @var MediaProcessor
     */
    protected $mediaProcessor;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        ContentObjectRenderer $contentObjectRenderer,
        HeadlineProcessor $headlineProcessor,
        BodyTextProcessor $bodyTextProcessor,
        CtaProcessor $ctaProcessor,
        MediaProcessor $mediaProcessor,
        LogManagerInterface $logManager
    ) {
        $this->contentObject = $contentObjectRenderer;
        $this->headlineProcessor = $headlineProcessor;
        $this->bodyTextProcessor = $bodyTextProcessor;
        $this->ctaProcessor = $ctaProcessor;
        $this->mediaProcessor = $mediaProcessor;
        $this->logger = $logManager->getLogger(__CLASS__);
    }

    public function process(array $data, array $configuration): ?array
    {
        $this->configuration = $configuration;
        $mediaItems = $this->getMediaItems($data);

        $twigData = [
            'uid' => $data['uid'],
            'header' => $this->getHeader($data),
            'space_before_class' => $data['space_before_class'],
            'space_after_class' => $data['space_after_class'],
            'bodytext' =>  $this->bodyTextProcessor->processBodyText($data),
            'tx_starter_cta' => $this->ctaProcessor->processCta($data),
            'tx_starter_backgroundcolor' => $data['tx_starter_backgroundcolor'],
            'tx_starter_imageorient' => self::IMAGE_POSITIONS[$data['imageorient']],
            'grid' => $this->getGrid($data, $mediaItems),
        ];

        return array_merge($twigData, $mediaItems);
    }

    protected function getHeader(array $data): array
    {
        return [
            'headline' => $this->headlineProcessor->processHeadline($data),
            'subline' => $this->headlineProcessor->processSubLine($data),
        ];
    }

    protected function getMediaItems(array $data): array
    {
        $resultMedia = [
            'image' => false,
            'video' => false,
        ];

        $imageConfig = $this->configuration['imageConfig'];
        $imageCropPosition = self::IMAGE_CROP_VARIANT[$data['imageorient']];
        if (isset($imageConfig['overrideRenderingByImageOrient'][$imageCropPosition])) {
            $imageConfig = $imageConfig['overrideRenderingByImageOrient'][$imageCropPosition];
        }

        $media = $this->mediaProcessor->renderMedia(
            $data,
            'tt_content',
            'assets',
            $imageConfig
        );

        if (count($media) == 1) {
            if ($media[0]['type'] == 'image') {
                $resultMedia['image'] = $media[0]['image'];
                $resultMedia['image']['uid'] = $media[0]['uid'];
            } else {
                $resultMedia['video'] = $media[0]['video'];
                $resultMedia['video']['uid'] = $media[0]['uid'];
            }
        }

        return $resultMedia;
    }

    protected function getGrid(array $data, array &$mediaItems): array
    {
        $items = null;

        if ($mediaItems['image']) {
            $items = &$mediaItems['image'];
        }

        if ($mediaItems['video']) {
            $items = &$mediaItems['video'];
        }

        if (is_null($items)) {
            return [];
        }

        $gridData = [
            'switchOrderOnSmall' => true,
            'showOnSmall' => $items['tx_starter_show_small'],
            'showOnMedium' => $items['tx_starter_show_medium'],
            'showOnLarge' => $items['tx_starter_show_large'],
            'imageCols' => [
                'small' => empty($data['imagecols']) ? 12 : (int)$data['imagecols'],
                'medium' => empty($data['tx_starter_imagecols_medium']) ? 6 : (int)$data['tx_starter_imagecols_medium'],
                'large' => empty($data['tx_starter_imagecols_large']) ? 6 : (int)$data['tx_starter_imagecols_large'],
            ],
            'textCols' => [
                'small' => $data['imagecols'] == 12 ? 12 : 12 - $data['imagecols'],
                'medium' => $data['tx_starter_imagecols_medium'] == 12 ? 12 : 12 - $data['tx_starter_imagecols_medium'],
                'large' => $data['tx_starter_imagecols_large'] == 12 ? 12 : 12 - $data['tx_starter_imagecols_large'],
            ],
        ];

        unset($items['tx_starter_show_small']);
        unset($items['tx_starter_show_medium']);
        unset($items['tx_starter_show_large']);

        return $gridData;
    }
}
