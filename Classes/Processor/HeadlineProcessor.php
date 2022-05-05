<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\Processor;

/**
 * Class HeadlineProcessor
 */
class HeadlineProcessor
{
    public function processHeadline(array $data): array
    {
        return [
            'header' => $this->getValue($data, 'header'),
            'header_position' => $this->getValue($data, 'header_position'),
            'header_layout' => $this->getValue($data, 'header_layout'),
            'tx_starter_headerfontsize' => $this->getValue($data, 'tx_starter_headerfontsize'),
            'tx_starter_headercolor' => $this->getValue($data, 'tx_starter_headercolor'),
        ];
    }

    public function processSubLine(array $data): array
    {
        return [
            'subheader' => $this->getValue($data, 'subheader'),
            'header_position' => $this->getValue($data, 'header_position'),
        ];
    }

    public function processOverLine(array $data): array
    {
        return [
            'tx_starter_overline' => $this->getValue($data, 'tx_starter_overline'),
            'header_position' => $this->getValue($data, 'header_position'),
        ];
    }

    protected function getValue(array $data, string $key): string
    {
        $value = '';
        if (isset($data[$key])) {
            $value = $data[$key];
        }

        return $value;
    }
}
