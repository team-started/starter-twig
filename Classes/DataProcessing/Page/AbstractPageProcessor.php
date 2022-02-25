<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\DataProcessing\Page;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class AbstractPageProcessor
 */
abstract class AbstractPageProcessor
{
    /**
     * The content object to render.
     * @var ContentObjectRenderer
     */
    public $cObj;

    /**
     * @var array
     */
    protected $conf = [];

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var TypoScriptService
     */
    protected $typoScriptService;

    /**
     * @var Context
     */
    protected $context;

    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->typoScriptService = $this->objectManager->get(TypoScriptService::class);
        $this->context = $this->objectManager->get(Context::class);
        $this->cObj = $this->objectManager->get(ContentObjectRenderer::class);
    }
}
