<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\Twig;

use StarterTeam\StarterTwig\Twig\Loader\Typo3Loader;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TwigEnvironment extends Environment implements SingletonInterface
{
    /**
     * @var array
     */
    protected $configuration = [];

    public function __construct()
    {
        $this->configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('starter_twig');

        $additionalLoaders = $this->getAdditionalLoaders();
        $loader = new ChainLoader($additionalLoaders);

        /**@var Typo3Loader $typo3Loader*/
        $typo3Loader = GeneralUtility::makeInstance(Typo3Loader::class);
        $loader->addLoader($typo3Loader);

        $storagePath = $this->getTemplateStoragePath();
        $namespaces = $this->getNamespaces();

        if (! empty($storagePath) || ! empty($namespaces)) {
            $fileSystemLoader = new FilesystemLoader();
            if (! empty($storagePath)) {
                $fileSystemLoader->addPath($storagePath);
            }

            foreach ($namespaces as $namespace => $path) {
                $fileSystemLoader->addPath($path, $namespace);
            }

            $loader->addLoader($fileSystemLoader);
        }

        parent::__construct($loader, [
            // fixme use TYPO3’s cache framework instead of filesystem for caching
            'cache' => $this->configuration['disableCache'] ? false : static::getCacheDirectory(),
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

    /**
     * @return LoaderInterface[]
     */
    protected function getAdditionalLoaders(): array
    {
        $loaderClasses = $this->configuration['loader'] ?: [];

        return array_map(fn (string $loaderClass) => GeneralUtility::makeInstance($loaderClass), $loaderClasses);
    }

    /**
     * @return string/null
     */
    protected function getTemplateStoragePath(): ?string
    {
        $rootTemplatePath = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(
            'starter_twig',
            'rootTemplatePath'
        );
        if (!isset($rootTemplatePath)) {
            return null;
        }

        return GeneralUtility::getFileAbsFileName($rootTemplatePath);
    }

    protected function getNamespaces()
    {
        try {
            $namespaces = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(
                'starter_twig',
                'namespaces'
            );
        } catch (ExtensionConfigurationExtensionNotConfiguredException $extensionConfigurationExtensionNotConfiguredException) {
            return [];
        } catch (ExtensionConfigurationPathDoesNotExistException $extensionConfigurationPathDoesNotExistException) {
            return [];
        }

        return array_map(
            '\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName',
            $namespaces
        );
    }
}
