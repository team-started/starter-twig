<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Starter Twig',
    'description' => '',
    'category' => 'plugin',
    'state' => 'stable',
    'constraints' => [
        'depends' => [
            'pti' => '3.0.0-3.99.99',
            'starter' => '*',
            'starter_sitepackage' => '*',
            'typo3' => '10.4.0-10.4.99',
        ],
        'suggests' => [],
    ],
];
