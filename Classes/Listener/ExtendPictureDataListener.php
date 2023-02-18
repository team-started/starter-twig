<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\Listener;

use PrototypeIntegration\PrototypeIntegration\Processor\Event\PictureProcessorRenderedEvent;

/**
 * Class ExtendPictureDataListener
 */
class ExtendPictureDataListener
{
    protected array $displayInformation = [
        'tx_starter_show_small' => true,
        'tx_starter_show_medium' => true,
        'tx_starter_show_large' => true,
    ];

    /**
     * Add display information for breakpoints to picture
     */
    public function addDisplayInformationForPictureData(PictureProcessorRenderedEvent $event): void
    {
        $result = $event->getResult();
        $asset = $event->getImage();
        $assetOptions = $this->displayInformation;

        foreach (array_keys($this->displayInformation) as $property) {
            if ($asset->hasProperty($property)) {
                $assetOptions[$property] = !(bool)$asset->getProperty($property);
            }
        }

        $result = array_merge($result, $assetOptions);
        $event->setResult($result);
    }
}
