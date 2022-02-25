<?php

defined('TYPO3') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'starter_twig',
    'Configuration/TypoScript',
    'Starter Twig'
);
