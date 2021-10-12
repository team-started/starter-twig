<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') || die();

ExtensionManagementUtility::addStaticFile(
    'starter_twig',
    'Configuration/TypoScript',
    'Starter Twig'
);
