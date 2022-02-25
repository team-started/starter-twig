<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\DataProcessing\Content;

trait AssetTrait
{
    /**
     * Image position definition
     */
    protected array $imagePosition = [
        0 => [
            'x' => 'center',
            'y' => 'above',
            'inside' => true
        ],
        8 => [
            'x' => 'center',
            'y' => 'below',
            'inside' => true
        ],
        17 => [
            'x' => 'right',
            'inside' => true
        ],
        18 => [
            'x' => 'left',
            'inside' => true
        ],
        25 => [
            'x' => 'right',
            'inside' => false
        ],
        26 => [
            'x' => 'left',
            'inside' => false
        ],
    ];

    /**
     * Image crop definition by image position
     */
    protected array $imageCropVariant = [
        0 => 'position-above-below',
        8 => 'position-above-below',
        17 => 'position-left-right',
        18 => 'position-left-right',
        25 => 'position-left-right',
        26 => 'position-left-right'
    ];

    protected function getGrid(array $data, ?array &$mediaItems): array
    {
        $items = null;

        if ($mediaItems['image']) {
            $items = &$mediaItems['image'];
        }

        if ($mediaItems['video']) {
            $items = &$mediaItems['video'];
        }

        if (is_null($items)) {
            return [];
        }

        $gridData = [
            'switchOrderOnSmall' => true,
            'showOnSmall' => $items['tx_starter_show_small'],
            'showOnMedium' => $items['tx_starter_show_medium'],
            'showOnLarge' => $items['tx_starter_show_large'],
            'imageCols' => [
                'small' => $this->getColumnSize($data['tx_starter_media_size_small']),
                'medium' => $this->getColumnSize($data['tx_starter_media_size_medium']),
                'large' => $this->getColumnSize($data['tx_starter_media_size_large']),
            ],
            'textCols' => [
                'small' => $this->getColumnSize($data['tx_starter_media_size_small'], 12, true),
                'medium' => $this->getColumnSize($data['tx_starter_media_size_medium'], 12, true),
                'large' => $this->getColumnSize($data['tx_starter_media_size_large'], 12, true),
            ]
        ];

        unset($items['tx_starter_show_small']);
        unset($items['tx_starter_show_medium']);
        unset($items['tx_starter_show_large']);

        return $gridData;
    }

    /**
     *
     * @param int|string $value
     */
    protected function getColumnSize($value, int $columnBase = 12, bool $calculateWithColumnBase = false): int
    {
        $value = (int)$value;

        $size = $value ?? $columnBase;

        if ($calculateWithColumnBase) {
            $size = $value ?? $columnBase - $value;
        }

        return $size;
    }

    protected function getImagePosition(int $imagePosition): ?array
    {
        return $this->imagePosition[$imagePosition] ?? null;
    }

    protected function getImageCropVariant(int $cropVariant = 0): ?string
    {
        return $this->imageCropVariant[$cropVariant] ?? null;
    }
}
