<?php

declare(strict_types=1);
namespace StarterTeam\StarterTwig\DataProcessing\Content;

use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;

/**
 * Class DividerProcessor
 */
class DividerProcessor implements PtiDataProcessor
{
    /**
     * @param array $data
     * @param array $configuration
     * @return array|null
     */
    public function process(array $data, array $configuration): ?array
    {
        $twigData = [
            'uid' => $data['uid'],
            'space_before_class' => $data['space_before_class'],
            'space_after_class' => $data['space_after_class'],
            'tx_starter_bordercolor' => $data['tx_starter_bordercolor'],
        ];

        return $twigData;
    }
}
