<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\DataProcessing\Content;

use Override;
use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use PrototypeIntegration\PrototypeIntegration\Processor\TypoLinkStringProcessor;
use Psr\Log\LoggerInterface;
use StarterTeam\StarterTwig\Processor\HeadlineProcessor;
use StarterTeam\StarterTwig\Service\RenderMediaService;
use TYPO3\CMS\Core\Log\LogManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class StarterCeMediaProcessor implements PtiDataProcessor
{
    protected array $configuration = [];

    protected LoggerInterface $logger;

    public function __construct(
        protected ContentObjectRenderer $contentObject,
        protected HeadlineProcessor $headlineProcessor,
        protected RenderMediaService $renderMediaService,
        protected TypoLinkStringProcessor $typoLinkProcessor,
        LogManagerInterface $logManager,
    ) {
        $this->logger = $logManager->getLogger(self::class);
    }

    #[Override]
    public function process(array $data, array $configuration): ?array
    {
        $this->configuration = $configuration;

        return [
            'uid' => $data['uid'],
            'CType' => str_replace('_', '-', $data['CType']),
            'header' => $this->headlineProcessor->processHeadline($data),
            'subheader' => $this->headlineProcessor->processSubLine($data),
            'overline' => $this->headlineProcessor->processOverLine($data),
            'space_before_class' => $data['space_before_class'],
            'space_after_class' => $data['space_after_class'],
            // columns are deprecated since 4.0.0-RC1 and would be remove in 5.0.0
            'columns' => [
                'small' => $this->getColumnValue($data['imagecols'], 1),
                'medium' => $this->getColumnValue($data['tx_starter_imagecols_medium']),
                'large' => $this->getColumnValue($data['tx_starter_imagecols_large']),
            ],
            'imagecols' => $this->getColumnValue($data['imagecols'], 1),
            'tx_starter_imagecols_medium' => $this->getColumnValue($data['tx_starter_imagecols_medium'], 1),
            'tx_starter_imagecols_large' => $this->getColumnValue($data['tx_starter_imagecols_large'], 1),
            'items' => $this->renderGalleryItems($data),
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

    protected function renderGalleryItems(array $data): array
    {
        $resultMedia = [];
        $imageConfig = $this->configuration['imageConfig'] ?? [];
        $imagePlaceholderConfig = $this->configuration['imageConfigPlaceholder'] ?? [];

        $mediaElements = $this->renderMediaService->processMedia(
            $data,
            'tt_content',
            'assets',
            $imageConfig,
            $imagePlaceholderConfig
        );

        foreach ($mediaElements as $index => $mediaElement) {
            if (isset($mediaElement['image']['metaData']['link']) &&
                !empty($mediaElement['image']['metaData']['link'])
            ) {
                $mediaElement['image']['metaData']['link'] =
                    $this->typoLinkProcessor->processTypoLinkString($mediaElement['image']['metaData']['link']);
            }

            if ($mediaElement['type'] == 'image') {
                $resultMedia[$index]['image'] = $mediaElement['image'];
                $resultMedia[$index]['image']['uid'] = $mediaElement['uid'];
                $resultMedia[$index]['image']['placeholder'] =
                    isset($mediaElement['thumbnail']) ? $mediaElement['thumbnail']['default'] : null;
            } else {
                $resultMedia[$index]['video'] = $mediaElement['video'];
                $resultMedia[$index]['video']['uid'] = $mediaElement['uid'];
            }
        }

        return $resultMedia;
    }

    /**
     * @param int|string $value
     * @param int|string $default
     * @return int|string
     */
    protected function getColumnValue(int|string $value, int|string $default = 'inherit'): int|string
    {
        if (($value === 0 || $value === '') && ($default !== 0 && $default !== '')) {
            return $default;
        }

        return $value;
    }
}
