<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\DataProcessing\Content;

use Override;
use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use StarterTeam\StarterTwig\DataProcessing\Page\MenuProcessorTrait;
use StarterTeam\StarterTwig\Processor\HeadlineProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class StarterDistributionNavigationProcessor implements PtiDataProcessor
{
    use MenuProcessorTrait;

    public const FALLBACK_TITLE_FIELD = 'nav_title // title';

    public function __construct(
        protected ContentObjectRenderer $contentObjectRenderer,
        protected HeadlineProcessor $headlineProcessor,
    ) {
    }

    #[Override]
    public function process(array $data, array $configuration): ?array
    {
        return [
            'uid' => $data['uid'],
            'CType' => str_replace('_', '-', $data['CType']),
            'header' => $this->headlineProcessor->processHeadline($data),
            'headerType' => $configuration['headerType'],
            'items' => $this->getSubPages((int)$data['pid'], $configuration),
        ];
    }

    private function getSubPages(int $pageUid, array $configuration): array
    {
        $menuConfiguration =             [
            'special' => 'directory',
            'special.' => [
                'value' => $pageUid,
            ],
            'levels' => 1,
            'expandAll' => 0,
            'titleField' => $configuration['menuConfiguration']['titleField'] ?? static::FALLBACK_TITLE_FIELD,
        ];

        return $this->getMenuFromCms($menuConfiguration, $this->contentObjectRenderer);
    }
}
