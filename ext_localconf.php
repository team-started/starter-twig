<?php

$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['starter_twig']['disableCache'] = false;
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['starter_twig']['rootTemplatePath'] = '';
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['starter_twig']['loader'][] =
    \StarterTeam\StarterTwig\Twig\Loader\FractalAliasLoader::class;
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['starter_twig']['namespaces'] = [];

$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['pti']['defaultView'] = \StarterTeam\StarterTwig\Twig\View\TwigView::class;
