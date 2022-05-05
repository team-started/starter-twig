<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\DataProcessing\Content;

use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;

/**
 * Class DividerProcessor
 */
class DividerProcessor implements PtiDataProcessor
{
    public function process(array $data, array $configuration): ?array
    {
        return [
            'uid' => $data['uid'],
            'CType' => str_replace('_', '-', $data['CType']),
            'space_before_class' => $data['space_before_class'],
            'space_after_class' => $data['space_after_class'],
            'tx_starter_visibility' => $data['tx_starter_visibility'],
            'tx_starter_bordercolor' => $data['tx_starter_bordercolor'],
            'tx_starter_background_fluid' => (bool) $data['tx_starter_background_fluid'],
            'tx_starter_container' => $data['tx_starter_width'],
        ];
    }
}
