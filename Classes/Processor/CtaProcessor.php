<?php

declare(strict_types=1);
namespace StarterTeam\StarterTwig\Processor;

use PrototypeIntegration\PrototypeIntegration\Processor\TypoLinkStringProcessor;

/**
 * Class CtaProcessor
 */
class CtaProcessor
{
    const CTA_LINK_FIELD = 'tx_starter_ctalink';
    const CTA_LINKTEXT_FIELD = 'tx_starter_ctalink_text';

    /**
     * @var TypoLinkStringProcessor
     */
    protected $linkProcessor;

    public function __construct(TypoLinkStringProcessor $typoLinkStringProcessor)
    {
        $this->linkProcessor = $typoLinkStringProcessor;
    }

    /**
     * @param array $data
     * @param string $linkField
     * @param string $linkTextField
     * @param bool $allowEmptyLinkText
     * @return array|null
     */
    public function processCta(
        array $data,
        string $linkField = self::CTA_LINK_FIELD,
        string $linkTextField = self::CTA_LINKTEXT_FIELD,
        bool $allowEmptyLinkText = false
    ): ?array {
        if (!$allowEmptyLinkText && (empty($data[$linkField]) || empty($data[$linkTextField]))) {
            return null;
        }

        $ctaData = [
            'linkText' => $data[$linkTextField],
            'link' => $this->linkProcessor->processTypoLinkString($data[$linkField]),
        ];

        return $ctaData;
    }
}
