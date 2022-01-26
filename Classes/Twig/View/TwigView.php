<?php

namespace StarterTeam\StarterTwig\Twig\View;

use PrototypeIntegration\PrototypeIntegration\View\TemplateBasedView;
use StarterTeam\StarterTwig\Twig\TwigEnvironment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\AbstractView;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

class TwigView extends AbstractView implements ViewInterface, TemplateBasedView
{
    /**
     * @var TwigEnvironment
     */
    protected $twigEnvironment;

    /**
     * @var string
     */
    protected $template;

    public function __construct(string $template = '')
    {
        $this->template = $template;
        $this->twigEnvironment = GeneralUtility::makeInstance(TwigEnvironment::class);
    }

    public function render(): string
    {
        if (empty($this->template)) {
            throw new \RuntimeException('Template file missing.', 1_519_205_250_412);
        }

        try {
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
}
