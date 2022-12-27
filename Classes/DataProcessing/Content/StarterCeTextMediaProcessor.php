<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\DataProcessing\Content;

use Psr\Log\LoggerInterface;
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
    use AssetTrait;

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
     * @var LoggerInterface
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
        $this->logger = $logManager->getLogger(self::class);
    }

    public function process(array $data, array $configuration): ?array
    {
        $this->configuration = $configuration;
        $mediaItems = $this->getMediaItems($data);

        $twigData = [
            'uid' => $data['uid'],
            'CType' => str_replace('_', '-', $data['CType']),
            'header' => $this->headlineProcessor->processHeadline($data),
            'subheader' => $this->headlineProcessor->processSubLine($data),
            'overline' => $this->headlineProcessor->processOverLine($data),
            'space_before_class' => $data['space_before_class'],
            'space_after_class' => $data['space_after_class'],
            'bodytext' =>  $this->bodyTextProcessor->processBodyText($data),
            'imageorient' => $this->getImagePosition((int)$data['imageorient']),
            'tx_starter_cta' => $this->ctaProcessor->processCta($data),
            'tx_starter_visibility' => $data['tx_starter_visibility'],
            'tx_starter_backgroundcolor' => $data['tx_starter_backgroundcolor'],
            'tx_starter_background_fluid' => (bool) $data['tx_starter_background_fluid'],
            'tx_starter_container' => $data['tx_starter_width'],
            'grid' => $this->getGrid($data, $mediaItems),
        ];

        return array_merge($twigData, $mediaItems);
    }

    /**
     * @deprecated since 4.0.0 and would be remove in 5.0.0
     */
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

        $imageConfig = $this->configuration['imageConfig'] ?? [];
        $imagePlaceholderConfig = $this->configuration['imageConfigPlaceholder'] ?? [];
        $imageCropPosition = $this->getImageCropVariant((int)$data['imageorient']);
        if (isset($imageConfig['overrideRenderingByImageOrient'][$imageCropPosition])) {
            $imageConfig = $imageConfig['overrideRenderingByImageOrient'][$imageCropPosition];
        }

        $media = $this->mediaProcessor->renderMedia(
            $data,
            'tt_content',
            'assets',
            $imageConfig,
            $imagePlaceholderConfig
        );

        if (count($media) == 1) {
            if ($media[0]['type'] == 'image') {
                $resultMedia['image'] = $media[0]['image'];
                $resultMedia['image']['uid'] = $media[0]['uid'];
                $resultMedia['image']['placeholder'] =
                    isset($media[0]['thumbnail']) ? $media[0]['thumbnail']['default'] : null;
            } else {
                $resultMedia['video'] = $media[0]['video'];
                $resultMedia['video']['uid'] = $media[0]['uid'];
            }
        }

        return $resultMedia;
    }
}
