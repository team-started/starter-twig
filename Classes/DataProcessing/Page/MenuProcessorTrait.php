<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\DataProcessing\Page;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\DataProcessing\MenuProcessor;

trait MenuProcessorTrait
{
    /**
     * @return mixed
     */
    protected function getMenuFromCms(array $processorConfiguration, ContentObjectRenderer &$cObject)
    {
        /** @var MenuProcessor $menuDataFetcher */
        $menuDataFetcher = GeneralUtility::makeInstance(MenuProcessor::class);

        return $menuDataFetcher->process($cObject, [], $processorConfiguration, [])['menu'];
    }
}
