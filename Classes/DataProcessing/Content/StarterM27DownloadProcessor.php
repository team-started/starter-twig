<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\DataProcessing\Content;

use Override;
use PrototypeIntegration\PrototypeIntegration\Processor\FileProcessor;
use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use Psr\Log\LoggerInterface;
use StarterTeam\StarterTwig\Processor\BodyTextProcessor;
use StarterTeam\StarterTwig\Processor\HeadlineProcessor;
use TYPO3\CMS\Core\Log\LogManagerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class StarterM27DownloadProcessor implements PtiDataProcessor
{
    public const string CONTENT_TABLE = 'tt_content';

    public const string CTYPE = 'starter_m27_download';

    public const array SORT_MAPPING = [
        'name' => 'title',
        'type' => 'fileType',
        'size' => 'fileSize',
    ];

    protected LoggerInterface $logger;

    protected array $configuration = [];

    public function __construct(
        protected FileProcessor $fileProcessor,
        protected BodyTextProcessor $bodyTextProcessor,
        protected HeadlineProcessor $headlineProcessor,
        LogManagerInterface $logManager,
    ) {
        $this->logger = $logManager->getLogger(self::class);
    }

    #[Override]
    public function process(array $data, array $configuration): ?array
    {
        $this->configuration = $configuration;
        $downloadFiles = $this->getDownloadFiles($data);

        if (is_null($downloadFiles)) {
            return null;
        }

        return [
            'uid' => $data['uid'],
            'CType' => str_replace('_', '-', $data['CType']),
            'header' => $this->headlineProcessor->processHeadline($data),
            'subheader' => $this->headlineProcessor->processSubLine($data),
            'overline' => $this->headlineProcessor->processOverLine($data),
            'space_before_class' => $data['space_before_class'],
            'space_after_class' => $data['space_after_class'],
            'bodytext' => $this->bodyTextProcessor->processBodyText($data),
            'tx_starter_backgroundcolor' => $data['tx_starter_backgroundcolor'],
            'tx_starter_background_fluid' => (bool)$data['tx_starter_background_fluid'],
            'tx_starter_visibility' => $data['tx_starter_visibility'],
            'tx_starter_container' => $data['tx_starter_width'],
            'items' => $downloadFiles,
        ];
    }

    protected function getDownloadFiles(array $data): ?array
    {
        $singleFiles = $this->fileProcessor->renderFileCollection(
            self::CONTENT_TABLE,
            'media',
            $data,
            $this->configuration
        );

        $collectionFiles = $this->fileProcessor->renderFilesFromCollection(
            GeneralUtility::trimExplode(',', $data['file_collections'], true),
            $this->configuration
        );

        $files = array_merge($singleFiles, $collectionFiles);
        $downloadFiles = [];
        foreach ($files as $fileIndex => $file) {
            if (!isset($file['link']['config'])) {
                $message = sprintf(
                    'Could not render an item of UID %s (CE-%s), file not exist',
                    $data['uid'],
                    self::CTYPE
                );
                $this->logger->warning($message);
                continue;
            }

            $downloadFiles[$fileIndex] = [
                'title' => $file['link']['metaData']['name'] ?? $file['link']['config']['title'],
                'description' => $data['uploads_description'] ? $file['link']['metaData']['description'] : '',
                'fileType' => $file['link']['metaData']['extension'],
                'fileSize' => $data['filelink_size'] ? $file['link']['metaData']['size'] : '',
                'link' => [
                    'config' => [
                        'uri' => $file['link']['config']['uri'],
                        'target' => $file['link']['config']['target'],
                        'class' => $file['link']['config']['class'],
                        'title' => $file['link']['metaData']['name'] ?? $file['link']['config']['title'],
                    ],
                ],
            ];
        }

        if (!empty($data['filelink_sorting'])) {
            $fieldToSort = (string)static::SORT_MAPPING[$data['filelink_sorting']];
            uasort($downloadFiles, fn($a, $b): int => strcmp((string)$a[$fieldToSort], (string)$b[$fieldToSort]));

            if ($data['filelink_sorting_direction'] === 'desc') {
                $downloadFiles = array_reverse($downloadFiles, true);
            }
        }

        return $downloadFiles;
    }
}
