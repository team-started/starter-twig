<?php

use StarterTeam\StarterTwig\Twig\Loader\FractalAliasLoader;
use StarterTeam\StarterTwig\Twig\Loader\Typo3Loader;
use StarterTeam\StarterTwig\Twig\View\TwigView;

defined('TYPO3') || die();

$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['starter_twig']['disableCache'] = false;
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['starter_twig']['rootTemplatePath'] = '';
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['starter_twig']['finderNotPath'] = [];
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['starter_twig']['namespaces'] = [];
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['starter_twig']['loader'] = [
    FractalAliasLoader::class,
    Typo3Loader::class,
];

$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['pti']['defaultView'] = TwigView::class;
