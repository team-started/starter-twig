<?php
declare(strict_types=1);
namespace StarterTeam\StarterTwig\DataProcessing;

interface PageDataProcessorInterface
{
    public function getLogoData(): ?array;

    public function getMainMenuData(): array;
}
