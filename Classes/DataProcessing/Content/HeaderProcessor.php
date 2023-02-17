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
    protected array $configuration = [];

    protected ContentObjectRenderer $contentObject;

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
            'header' => $this->headlineProcessor->processHeadline($data),
            'subheader' => $this->headlineProcessor->processSubLine($data),
            'overline' => $this->headlineProcessor->processOverLine($data),
            'space_before_class' => $data['space_before_class'],
            'space_after_class' => $data['space_after_class'],
            'tx_starter_visibility' => $data['tx_starter_visibility'],
            'tx_starter_backgroundcolor' => $data['tx_starter_backgroundcolor'],
            'tx_starter_background_fluid' => (bool) $data['tx_starter_background_fluid'],
            'tx_starter_container' => $data['tx_starter_width'],
        ];
    }
}
