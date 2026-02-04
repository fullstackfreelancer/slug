<?php
use KOHLERCODE\Slug\Controller\SlugController;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

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

$moduleArray = [
    'slug_modules' => [
        'labels' => 'LLL:EXT:slug/Resources/Private/Language/locallang_module_category.xlf',
        'iconIdentifier' => 'module-category-slug',
        'position' => ['after' => 'web'],
    ],
    'slug_list' => [
        'parent' => 'slug_modules',
        'standalone'=> true,
        'position' => ['before' => '*'],
        'access' => 'user,group',
        'workspaces' => 'live',
        'path' => '/module/',
        'iconIdentifier' => 'module-slug',
        'labels' => 'LLL:EXT:slug/Resources/Private/Language/locallang_module_list.xlf',
        'extensionName' => 'slug',
        'controllerActions' => [
            SlugController::class => [
                'list',
                'record'
            ],
        ],
        'moduleData' => [
            'language' => 0,
        ],
    ]
];

if(ExtensionManagementUtility::isLoaded('slugpro')){
    $moduleArray['slug_page'] = [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user,group',
        'workspaces' => 'live',
        'path' => '/module/slug/',
        'iconIdentifier' => 'module-slug',
        'labels' => 'LLL:EXT:slug/Resources/Private/Language/locallang_module_page.xlf',
        'extensionName' => 'slug',
        'controllerActions' => [
            SlugController::class => [
                'page'
            ],
        ],
    ];
}

return $moduleArray;