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

    /**
     * @param array $data
     * @param array $configuration
     * @return array[]|null
     */
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
        $contentHtml = $this->cObj->cObjGetSingle('< styles.content.get', []);

        return $contentHtml;
    }

    /**
     * @return array|null
     */
    public function getLogoData(): ?array
    {
        return [];
    }

    /**
     * @return array|null
     */
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
