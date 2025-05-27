<?php
use SIMONKOEHLER\Slug\Controller\PageController;
use SIMONKOEHLER\Slug\Controller\ExtensionController;

/**
 * Module configuration for the Slug extension.
 *
 * This file returns an array defining backend modules provided by the Slug extension.
 * It includes module categories and individual modules with their properties such as
 * access permissions, workspace compatibility, routing paths, icons, labels, and
 * associated controller actions.
 *
 * The array keys represent module identifiers, each configured with metadata needed
 * by the TYPO3 backend module system.
 *
 * @return array<string, array<string, mixed>>
 */
return [
    'slug_modules' => [
        'labels' => 'LLL:EXT:slug/Resources/Private/Language/locallang_module_category.xlf',
        'iconIdentifier' => 'module-category-slug',
        'position' => ['after' => 'web'],
    ],
    'slug_page' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user,group',
        'workspaces' => 'live',
        'path' => '/module/slug/page',
        'iconIdentifier' => 'module-slug',
        'labels' => 'LLL:EXT:slug/Resources/Private/Language/locallang_module_page.xlf',
        'extensionName' => 'Slug',
        'controllerActions' => [
            PageController::class => [
                'page',
            ],
        ],
    ],
    'slug_list' => [
        'parent' => 'slug_modules',
        'standalone'=> true,
        'position' => ['before' => '*'],
        'access' => 'user,group',
        'workspaces' => 'live',
        'path' => '/module/slug/list',
        'iconIdentifier' => 'module-slug',
        'labels' => 'LLL:EXT:slug/Resources/Private/Language/locallang_module_list.xlf',
        'extensionName' => 'Slug',
        'controllerActions' => [
            PageController::class => [
                'list',
                'tree'
            ],
            ExtensionController::class => [
                'list'
            ],
        ],
        'moduleData' => [
            'language' => 0,
        ],
    ]
];
