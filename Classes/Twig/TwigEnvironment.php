<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\Twig;

use Exception;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Extension\DebugExtension;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TwigEnvironment extends Environment implements SingletonInterface
{
    protected array $configuration = [];

    /**
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws LoaderError
     * @todo fixme use TYPO3’s cache framework instead of filesystem for caching
     */
    public function __construct()
    {
        $this->loadConfiguration();

        $additionalLoaders = array_merge($this->getAdditionalLoaders(), [$this->defineFileSystemLoader()]);
        $loader = new ChainLoader($additionalLoaders);

        parent::__construct($loader, [
            'cache' => $this->getConfigurationWithKey('disableCache') ? false : static::getCacheDirectory(),
            'debug' => $GLOBALS['TYPO3_CONF_VARS']['FE']['debug'],
        ]);

        if ($this->isDebug()) {
            $this->addExtension(new DebugExtension());
        }

        $this->addGlobal('env', 'CMS');
    }

    /**
     * Returns the path to the twig cache directory.
     */
    public static function getCacheDirectory(): string
    {
        return \TYPO3\CMS\Core\Core\Environment::getVarPath() . '/cache/code/twig/';
    }

    protected function getAdditionalLoaders(): array
    {
        $loaderClasses = $this->configuration['loader'] ?? [];
        if ($loaderClasses === []) {
            return [];
        }

        return array_map(fn (string $loaderClass) => GeneralUtility::makeInstance($loaderClass), $loaderClasses);
    }

    /**
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws Exception
     */
    protected function getNamespaces(): array
    {
        $namespaces = $this->getConfigurationWithKey('namespaces');

        if (!is_array($namespaces)) {
            throw new Exception('Namespaces must configured as array', 1_676_655_866);
        }

        return array_map(
            '\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName',
            $namespaces
        );
    }

    /**
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    private function getTemplatePath(): ?string
    {
        $templatePath = $this->getConfigurationWithKey('rootTemplatePath');
        if (is_string($templatePath)) {
            return GeneralUtility::getFileAbsFileName($templatePath);
        }

        return null;
    }

    /**
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws LoaderError
     */
    private function defineFileSystemLoader(): FilesystemLoader
    {
        $fileSystemLoader = new FilesystemLoader();

        $storagePath = $this->getTemplatePath();
        if ($storagePath !== null && $storagePath !== '') {
            $fileSystemLoader->addPath($storagePath);
        }

        $namespaces = $this->getNamespaces();
        foreach ($namespaces as $namespace => $path) {
            $fileSystemLoader->addPath($path, $namespace);
        }

        return $fileSystemLoader;
    }

    /**
     * @return array|string|null
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    private function getConfigurationWithKey(string $key)
    {
        if ($this->configuration === []) {
            $this->loadConfiguration();
        }

        if (array_key_exists($key, $this->configuration)) {
            return $this->configuration[$key];
        }

        return null;
    }

    /**
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
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
}
