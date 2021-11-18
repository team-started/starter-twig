<?php

declare(strict_types=1);
namespace StarterTeam\StarterTwig\DataProcessing\Content;

use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use StarterTeam\StarterTwig\Processor\BodyTextProcessor;
use StarterTeam\StarterTwig\Processor\CtaProcessor;
use StarterTeam\StarterTwig\Processor\HeadlineProcessor;
use TYPO3\CMS\Core\Log\LogManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class TextProcessor
 */
class TextProcessor implements PtiDataProcessor
{
    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * @var HeadlineProcessor
     */
    protected $headlineProcessor;

    /**
     * @var BodyTextProcessor
     */
    protected $bodyTextProcessor;

    /**
     * @var CtaProcessor
     */
    protected $ctaProcessor;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        ContentObjectRenderer $contentObjectRenderer,
        HeadlineProcessor $headlineProcessor,
        BodyTextProcessor $bodyTextProcessor,
        CtaProcessor $ctaProcessor,
        LogManagerInterface $logManager
    ) {
        $this->contentObject = $contentObjectRenderer;
        $this->headlineProcessor = $headlineProcessor;
        $this->bodyTextProcessor = $bodyTextProcessor;
        $this->ctaProcessor = $ctaProcessor;
        $this->logger = $logManager->getLogger(__CLASS__);
    }

    public function process(array $data, array $configuration): ?array
    {
        $this->configuration = $configuration;

        $twigData = [
            'uid' => $data['uid'],
            'header' => $this->getHeader($data),
            'space_before_class' => $data['space_before_class'],
            'space_after_class' => $data['space_after_class'],
            'bodytext' =>  $this->bodyTextProcessor->processBodyText($data),
            'tx_starter_cta' => $this->ctaProcessor->processCta($data),
            'tx_starter_backgroundcolor' => $data['tx_starter_backgroundcolor'],
        ];

        return $twigData;
    }

    protected function getHeader(array $data): array
    {
        $header = [
            'headline' => $this->headlineProcessor->processHeadline($data),
            'subline' => $this->headlineProcessor->processSubLine($data),
        ];

        return $header;
    }
}
