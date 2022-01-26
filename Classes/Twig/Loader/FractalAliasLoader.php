<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\Twig\Loader;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Loads templates using alias names. Alias are a concept of Frctl (https://fractal.build/)
 *
 * This loader supports alias names for Templates.
 * For example you can load a template like this: @headline
 */
class FractalAliasLoader extends FilesystemLoader
{
    /**
     * @var null
     */
    private $templatePath;

    private array $aliases = [];

    public function __construct(string $templateRootPath = null)
    {
        if ($templateRootPath) {
            $this->templatePath = GeneralUtility::getFileAbsFileName($templateRootPath);
        }

        if (is_null($this->templatePath)) {
            $this->templatePath = $this->getTemplateStoragePath();
        }

        if ($this->templatePath === null) {
            $this->errorCache[$this->templatePath] = 'There was no template path found for FractalAliasLoader.';

            throw new LoaderError($this->errorCache[$this->templatePath]);
        }

        parent::__construct([$this->templatePath], null);

        foreach ($this->paths as $path) {
            $finder = new Finder();
            $files = $finder
                ->files()
                ->in($path)
                ->name('*.twig')
                ->followLinks();

            /** @var SplFileInfo $file */
            foreach ($files as $file) {
                $handle = basename($file->getBasename(), '.' . $file->getExtension());
                $handle = '@' . strtolower($handle);
                $this->aliases[$handle] = $file->getPathname();
            }
        }
    }

    /**
     * Checks if the template alias was found during initialization
     *
     * @param string $name The template name
     * @param bool $throw Whether to throw an exception when an error occurs
     * @return string|null The template name or null
     * @throws LoaderError
     */
    protected function findTemplate(string $name, bool $throw = true)
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        if (isset($this->errorCache[$name])) {
            if (!$throw) {
                return false;
            }

            throw new LoaderError($this->errorCache[$name]);
        }

        $path = $this->resolveAlias($name);
        if (!empty($path) && \is_file($path)) {
            return $this->cache[$name] = $path;
        }

        $this->errorCache[$name] = \sprintf('Unable to find template "%s".', $name);
        if (!$throw) {
            return false;
        }

        throw new LoaderError($this->errorCache[$name]);
    }

    private function resolveAlias(string $name)
    {
        if (array_key_exists($name, $this->aliases)) {
            return $this->aliases[$name];
        }

        return false;
    }

    protected function getTemplateStoragePath(): ?string
    {
        $rootTemplatePath = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('starter_twig', 'rootTemplatePath');
        if (!isset($rootTemplatePath)) {
            return null;
        }

        return GeneralUtility::getFileAbsFileName($rootTemplatePath);
    }
}
