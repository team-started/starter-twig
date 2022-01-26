<?php

defined('TYPO3_MODE') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'starter_twig',
    'Configuration/TypoScript',
    'Starter Twig'
);
