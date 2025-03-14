<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\Processor;

use PrototypeIntegration\PrototypeIntegration\Processor\RichtextProcessor;

class BodyTextProcessor
{
    public const string DEFAULT_DATA_FIELD_NAME = 'bodytext';

    public function __construct(
        protected RichtextProcessor $rteProcessor,
    ) {
    }

    public function processBodyText(array $data, string $dataField = self::DEFAULT_DATA_FIELD_NAME): string
    {
        if ($dataField === '') {
            $dataField = self::DEFAULT_DATA_FIELD_NAME;
        }

        return $this->rteProcessor->processRteText($data[$dataField]);
    }

    public function processPlainBodyText(array $data, string $dataField = self::DEFAULT_DATA_FIELD_NAME): string
    {
        $value = '';

        if ($dataField === '') {
            $dataField = self::DEFAULT_DATA_FIELD_NAME;
        }

        if (array_key_exists($dataField, $data) && $data[$dataField] !== '') {
            $value = strip_tags((string)$data[$dataField]);
        }

        return $value;
    }
}
