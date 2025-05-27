<?php
use SIMONKOEHLER\Slug\Controller;

/**
 * Route configuration for the Slug extension.
 *
 * This array defines the API endpoints (path => controller action) used for AJAX requests
 * within the Slug extension. Each route maps a URL path to a specific method in the
 * AjaxController class.
 *
 * @return array<string, array{path: string, target: string}>
 */
return [
    'slug_list' => [
        'path' => '/slug/list',
        'target' => Controller\AjaxController::class . '::listAction'
    ],
    'slug_save_page' => [
        'path' => '/slug/savePageSlug',
        'target' => Controller\AjaxController::class . '::savePageSlug'
    ],
    'slug_info' => [
        'path' => '/slug/slugInfo',
        'target' => Controller\AjaxController::class . '::slugInfo'
    ],
    'slug_exists' => [
        'path' => '/slug/slugExists',
        'target' => Controller\AjaxController::class . '::slugExists'
    ],
    'slug_generate' => [
        'path' => '/slug/generatePageSlug',
        'target' => Controller\AjaxController::class . '::getPageSlug'
    ],
    'slug_pagedata' => [
        'path' => '/slug/getPageData',
        'target' => Controller\AjaxController::class . '::getPageData'
    ],
    'slug_save_record' => [
        'path' => '/slug/saveRecordSlug',
        'target' => Controller\AjaxController::class . '::saveRecordSlug'
    ],
    'slug_generate_record' => [
        'path' => '/slug/generateRecordSlug',
        'target' => Controller\AjaxController::class . '::generateRecordSlug'
    ],
    'slug_tree_items' => [
        'path' => '/slug/loadTreeItemSlugs',
        'target' => Controller\AjaxController::class . '::loadTreeItemSlugs'
    ],
    'slug_update_page_title' => [
        'path' => '/slug/savePageTitle',
        'target' => Controller\AjaxController::class . '::updatePageTitle'
    ],
];
