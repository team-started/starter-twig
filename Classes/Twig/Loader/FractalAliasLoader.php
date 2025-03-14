<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\Twig\Loader;

use Override;
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
    private string $templatePath;

    private array $configuration = [];

    private array $aliases = [];

    /**
     * @throws LoaderError
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function __construct(
        ?string $rootPath = null,
    ) {
        $this->loadConfiguration();
        $this->setTemplatePath($rootPath);

        parent::__construct([$this->templatePath]);

        $this->setAlias();
    }

    /**
     * Checks if the template alias was found during initialization
     */
    #[Override]
    protected function findTemplate(string $name, bool $throw = true): ?string
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        if (isset($this->errorCache[$name])) {
            if (!$throw) {
                return null;
            }

            throw new LoaderError($this->errorCache[$name], 4190286639);
        }

        $path = $this->resolveAlias($name);
        if (is_string($path) && $path !== '' && \is_file($path)) {
            return $this->cache[$name] = $path;
        }

        $this->errorCache[$name] = \sprintf('Unable to find template "%s".', $name);
        if (!$throw) {
            return null;
        }

        throw new LoaderError($this->errorCache[$name], 7251041687);
    }

    private function resolveAlias(string $name): bool|string
    {
        if (array_key_exists($name, $this->aliases)) {
            return $this->aliases[$name];
        }

        return false;
    }

    private function loadConfiguration(): void
    {
        $configuration = [];
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('starter_twig');

        if (!is_array($extensionConfiguration)) {
            $configuration[] = $extensionConfiguration;
        } else {
            $configuration = $extensionConfiguration;
        }

        $this->configuration = $configuration;
    }

    private function setTemplatePath(?string $templateRootPath): void
    {
        if ($templateRootPath) {
            $this->templatePath = GeneralUtility::getFileAbsFileName($templateRootPath);
        }

        if ($this->templatePath === '') {
            $templatePath = $this->getConfigurationWithKey('rootTemplatePath');
            if (is_null($templatePath)) {
                $this->errorCache[$this->templatePath] = 'There was no template path found under configuration key "rootTemplatePath" for FractalAliasLoader.';
                throw new LoaderError($this->errorCache[$this->templatePath], 1814776903);
            }

            if (is_array($templatePath)) {
                $this->errorCache[$this->templatePath] = 'Template path expects string value, array given for configuration key "rootTemplatePath" for FractalAliasLoader.';
                throw new LoaderError($this->errorCache[$this->templatePath], 3254363209);
            }

            $this->templatePath = GeneralUtility::getFileAbsFileName($templatePath);
        }

        if ($this->templatePath === '') {
            $this->errorCache[$this->templatePath] = 'There was no template path found for FractalAliasLoader.';
            throw new LoaderError($this->errorCache[$this->templatePath], 7887241089);
        }
    }

    private function setAlias(): void
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

    private function getConfigurationWithKey(string $key): array|string|null
    {
        if ($this->configuration === []) {
            $this->loadConfiguration();
        }

        if (array_key_exists($key, $this->configuration)) {
            return $this->configuration[$key];
        }

        return null;
    }
}
