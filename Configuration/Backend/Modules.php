<?php
use SIMONKOEHLER\Slug\Controller\PageController;
use SIMONKOEHLER\Slug\Controller\ExtensionController;

/**
 * Definitions for modules provided by EXT:examples
 */
return [
    'slug_web' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user,group',
        'workspaces' => 'live',
        'path' => '/module/slug/page',
        'iconIdentifier' => 'module-slug',
        'labels' => 'LLL:EXT:slug/Resources/Private/Language/locallang_slugs.xlf',
        'extensionName' => 'Slug',
        'controllerActions' => [
            PageController::class => [
                'page',
            ],
        ],
    ],
    'slug_sites' => [
        'parent' => 'site',
        'standalone'=> true,
        'position' => ['after' => 'web_info'],
        'access' => 'user,group',
        'workspaces' => 'live',
        'path' => '/module/slug/site',
        'iconIdentifier' => 'module-slug',
        'labels' => 'LLL:EXT:slug/Resources/Private/Language/locallang_slugs.xlf',
        'extensionName' => 'Slug',
        'controllerActions' => [
            PageController::class => [
                'ajaxList',
            ],
        ],
        'moduleData' => [
            'language' => 0,
        ],
    ]
];
