<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\DataProcessing\Content;

use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use StarterTeam\StarterTwig\Processor\HeadlineProcessor;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class HeaderProcessor
 */
class HeaderProcessor implements PtiDataProcessor
{
    /**
     * @var array
     */
    protected array $configuration = [];

    /**
     * @var ContentObjectRenderer
     */
    protected ContentObjectRenderer $contentObject;

    /**
     * @var HeadlineProcessor
     */
    protected HeadlineProcessor $headlineProcessor;

    public function __construct(
        ContentObjectRenderer $contentObjectRenderer,
        HeadlineProcessor $headlineProcessor
    ) {
        $this->contentObject = $contentObjectRenderer;
        $this->headlineProcessor = $headlineProcessor;
    }

    public function process(array $data, array $configuration): ?array
    {
        $this->configuration = $configuration;

        return [
            'uid' => $data['uid'],
            'CType' => str_replace('_', '-', $data['CType']),
            'header' => [
                'headline' => $this->headlineProcessor->processHeadline($data),
                'subline' => $this->headlineProcessor->processSubLine($data),
            ],
            'space_before_class' => $data['space_before_class'],
            'space_after_class' => $data['space_after_class'],
            'tx_starter_backgroundcolor' => $data['tx_starter_backgroundcolor'],
        ];
    }
}
