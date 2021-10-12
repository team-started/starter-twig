<?php
namespace StarterTeam\StarterTwig\Listener;

use PrototypeIntegration\PrototypeIntegration\Processor\Event\PictureProcessorRenderedEvent;

/**
 * Class ExtendPictureDataListener
 */
class ExtendPictureDataListener
{
    protected $displayInformation = [
        'tx_starter_show_small' => true,
        'tx_starter_show_medium' => true,
        'tx_starter_show_large' => true,
    ];

    /**
     * Add display information for breakpoints to picture
     *
     * @param PictureProcessorRenderedEvent $event
     */
    public function addDisplayInformationForPictureData(PictureProcessorRenderedEvent $event)
    {
        $result = $event->getResult();
        $asset = $event->getImage();
        $assetOptions = $this->displayInformation;

        foreach ($this->displayInformation as $property => $value) {
            if ($asset->hasProperty($property)) {
                $assetOptions[$property] = !(bool) $asset->getProperty($property);
            }
        }

        $result = array_merge($result, $assetOptions);
        $event->setResult($result);
    }
}
