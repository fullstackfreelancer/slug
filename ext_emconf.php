<?php
$EM_CONF['slug'] = [
    'title' => 'Slug',
    'description' => 'TYPO3 backend module for efficient management of URL slugs across pages and records, focused on SEO, bulk operations, and editorial productivity.',
    'category' => 'module',
    'author' => 'Simon Köhler',
    'author_email' => 'simon@kohlercode.com',
    'company' => 'kohlercode.com',
    'state' => 'alpha',
    'clearCacheOnLoad' => true,
    'version' => '5.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.1.0 - 14.1.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'KOHLERCODE\\Slug\\' => 'Classes',
        ],
    ],
];
