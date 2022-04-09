<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\Twig\Loader;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Loads templates using alias names. Alias are a concept of Fractal (https://fractal.build/)
 *
 * This loader supports alias names for Templates.
 * For example, you can load a template like this @headline instead of /path/sub-path/headline.twig
 */
class FractalAliasLoader extends FilesystemLoader
{
    private ?string $templatePath = null;

    private ?array $configuration = null;

    private array $aliases = [];

    /**
     * @throws LoaderError
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function __construct(?string $templateRootPath = null)
    {
        $this->loadConfiguration();
        $this->setTemplatePath($templateRootPath);

        parent::__construct([$this->templatePath]);

        $this->setAlias();
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

    /**
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    private function loadConfiguration()
    {
        $this->configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('starter_twig');
    }

    /**
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws LoaderError
     */
    private function setTemplatePath(?string $templateRootPath)
    {
        if ($templateRootPath) {
            $this->templatePath = GeneralUtility::getFileAbsFileName($templateRootPath);
        }

        if (empty($this->templatePath)) {
            $templatePath = $this->getConfigurationWithKey('rootTemplatePath');
            $this->templatePath = GeneralUtility::getFileAbsFileName($templatePath);
        }

        if (empty($this->templatePath)) {
            $this->errorCache[$this->templatePath] = 'There was no template path found for FractalAliasLoader.';
            throw new LoaderError($this->errorCache[$this->templatePath]);
        }
    }

    /**
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    private function setAlias()
    {
        foreach ($this->paths as $path) {
            $finder = new Finder();
            if (!is_null($notPathConfiguration = $this->getConfigurationWithKey('finderNotPath'))) {
                $finder->notPath($notPathConfiguration);
            }

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
     * @return null|mixed
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    private function getConfigurationWithKey(string $key)
    {
        if (is_null($this->configuration)) {
            $this->loadConfiguration();
        }

        if (array_key_exists($key, $this->configuration)) {
            return $this->configuration[$key];
        }

        return null;
    }
}
