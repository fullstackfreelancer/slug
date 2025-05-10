<?php

$EM_CONF['slug'] = [
    'title' => 'Slug',
    'description' => 'Helps managing the URL slugs of your TYPO3 pages and custom records',
    'category' => 'module',
    'author' => 'Simon Köhler',
    'author_email' => 'simon@kohlercode.com',
    'company' => 'kohlercode.com',
    'state' => 'alpha',
    'clearCacheOnLoad' => true,
    'version' => '5.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            // The prefix must end with a backslash
            'SIMONKOEHLER\\Slug\\' => 'Classes',
        ],
    ],
];
