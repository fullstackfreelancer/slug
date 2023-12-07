<?php

$EM_CONF['slug'] = [
    'title' => 'Slug',
    'description' => 'Helps managing the URL slugs of your TYPO3 pages and custom records!',
    'category' => 'module',
    'author' => 'Simon Köhler',
    'author_email' => 'info@simonkoehler.com',
    'company' => 'simonkoehler.com',
    'state' => 'alpha',
    'clearCacheOnLoad' => true,
    'version' => '5.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.99.99-12.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
