<?php

$EM_CONF['starter_twig'] = [
    'title' => 'Starter Twig',
    'description' => 'TYPO3 data processing to handle twig templates',
    'category' => 'plugin',
    'state' => 'stable',
    'constraints' => [
        'depends' => [
            'pti' => '3.0.0-3.99.99',
            'starter' => '*',
            'typo3' => '10.4.0-10.4.99',
        ],
        'suggests' => [],
    ],
];
