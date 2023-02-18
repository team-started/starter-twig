<?php

declare(strict_types=1);

namespace StarterTeam\StarterTwig\Twig\Loader;

use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Adds the possibility to load twig files from TYPO3 extensions.
 */
class Typo3Loader implements LoaderInterface
{
    protected array $cache = [];

    protected array $errorCache = [];

    public function getSourceContext(string $name): Source
    {
        $path = $this->findTemplate($name);
        $content = file_get_contents($path);

        if (is_bool($content)) {
            $this->errorCache[$name] = sprintf('Unable to get content of template "%s" in path "%s".', $name, $path);
            throw new LoaderError($this->errorCache[$name]);
        }

        return new Source($content, $name, $path);
    }

    public function getCacheKey(string $name): string
    {
        return $name;
    }

    public function isFresh(string $name, int $time): bool
    {
        return \filemtime($this->findTemplate($name)) <= $time;
    }

    public function exists(string $name): bool
    {
        if (isset($this->cache[$name])) {
            return true;
        }

        try {
            $this->findTemplate($name);
            return true;
        } catch (LoaderError $loaderError) {
            return false;
        }
    }

    /**
     * Checks if the template can be found.
     *
     * @return string The template name or false
     * @throws LoaderError
     */
    public function findTemplate(string $name): string
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        if (isset($this->errorCache[$name])) {
            throw new LoaderError($this->errorCache[$name]);
        }

        $path = GeneralUtility::getFileAbsFileName($name);

        if (empty($path) || !\is_file($path)) {
            $this->errorCache[$name] = \sprintf('unable to find template "%s".', $name);
            throw new LoaderError($this->errorCache[$name]);
        }

        $this->cache[$name] = $path;

        return $path;
    }
}
