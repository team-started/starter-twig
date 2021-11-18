<?php

declare(strict_types=1);
namespace StarterTeam\StarterTwig\Processor;

/**
 * Class HeadlineProcessor
 */
class HeadlineProcessor
{
    /**
     * @param array $data
     * @return string[]
     */
    public function processHeadline(array $data): array
    {
        $headlineFields = [
            'header' => $this->getValue($data, 'header'),
            'header_position' => $this->getValue($data, 'header_position'),
            'header_layout' => $this->getValue($data, 'header_layout'),
            'tx_starter_headerfontsize' => $this->getValue($data, 'tx_starter_headerfontsize'),
            'tx_starter_headercolor' => $this->getValue($data, 'tx_starter_headercolor'),
        ];

        return $headlineFields;
    }

    /**
     * @param array $data
     * @return array
     */
    public function processSubLine(array $data): array
    {
        $subLineFields = [
            'subheader' => $this->getValue($data, 'subheader'),
            'header_position' => $this->getValue($data, 'header_position'),
        ];

        return $subLineFields;
    }

    /**
     * @param array $data
     * @param string $key
     * @return string
     */
    protected function getValue(array $data, string $key): string
    {
        $value = '';
        if (isset($data[$key])) {
            $value = $data[$key];
        }

        return $value;
    }
}
