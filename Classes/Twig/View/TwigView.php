<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\Twig\View;

use Exception;
use Override;
use PrototypeIntegration\PrototypeIntegration\View\PtiViewInterface;
use PrototypeIntegration\PrototypeIntegration\View\TemplateBasedViewInterface;
use RuntimeException;
use StarterTeam\StarterTwig\Twig\TwigEnvironment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TwigView implements PtiViewInterface, TemplateBasedViewInterface
{
    protected TwigEnvironment $twigEnvironment;

    protected array $variables = [];

    public function __construct(protected string $template = '')
    {
        $this->twigEnvironment = GeneralUtility::makeInstance(TwigEnvironment::class);
    }

    #[Override]
    public function render(): string
    {
        if ($this->template === '') {
            throw new RuntimeException('Template file missing.', 1519205250412);
        }

        try {
            $this->moveCustomGlobalsIntoTwigEnvironment();
            return $this->twigEnvironment->render($this->template, $this->variables);
        } catch (Exception $exception) {
            throw new RuntimeException('Twig view error: ' . $exception->getMessage(), 1_519_205_228_169, $exception);
        }
    }

    #[Override]
    public function getTemplate(): string
    {
        return $this->template;
    }

    #[Override]
    public function setTemplate(string $templateIdentifier): void
    {
        $this->template = $templateIdentifier;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    #[Override]
    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }

    public function moveCustomGlobalsIntoTwigEnvironment(): void
    {
        if ($this->variables !== [] && array_key_exists('_globals', $this->variables)) {
            $this->twigEnvironment->addGlobal('_globals', $this->variables['_globals']);
            unset($this->variables['_globals']);
        }
    }
}
