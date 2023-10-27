<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\Twig\View;

use PrototypeIntegration\PrototypeIntegration\View\TemplateBasedView;
use StarterTeam\StarterTwig\Twig\TwigEnvironment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\AbstractView;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

class TwigView extends AbstractView implements ViewInterface, TemplateBasedView
{
    protected TwigEnvironment $twigEnvironment;

    protected string $template;

    public function __construct(string $template = '')
    {
        $this->template = $template;
        $this->twigEnvironment = GeneralUtility::makeInstance(TwigEnvironment::class);
    }

    public function render(): string
    {
        if ($this->template === '') {
            throw new \RuntimeException('Template file missing.', 1_519_205_250_412);
        }

        try {
            $this->moveCustomGlobalsIntoTwigEnvironment();
            return $this->twigEnvironment->render($this->template, $this->variables);
        } catch (\Exception $exception) {
            throw new \RuntimeException('Twig view error: ' . $exception->getMessage(), 1_519_205_228_169, $exception);
        }
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

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
