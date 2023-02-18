<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\DataProcessing\Page;

use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use StarterTeam\StarterTwig\DataProcessing\PageDataProcessorInterface;

/**
 * Class PageProcessor
 */
class PageProcessor extends AbstractPageProcessor implements PtiDataProcessor, PageDataProcessorInterface
{
    use MenuProcessorTrait;

    public function process(array $data, array $configuration = []): ?array
    {
        $this->conf = $configuration;

        $viewData = [
            'pageData' => $data,
            'logoData' => $this->getLogoData(),
            'navigationData' => $this->getMainMenuData(),
            'siteFooterData' => $this->getSiteFooterData(),
            'contentHtml' => $this->getContentHtml(),
        ];

        unset($data);

        return $viewData;
    }

    protected function getContentHtml(): string
    {
        return $this->cObj->cObjGetSingle('< styles.content.get', []);
    }

    public function getLogoData(): ?array
    {
        return [];
    }

    public function getSiteFooterData(): ?array
    {
        return [];
    }

    public function getMainMenuData(): array
    {
        $mainMenuSettings = $this->conf['menuConfiguration'];
        $menuData = $this->getMenuFromCms($mainMenuSettings, $this->cObj);

        return [
            'items' => $menuData,
        ];
    }
}
