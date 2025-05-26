<?php
use SIMONKOEHLER\Slug\Controller;

/*
 * This file was created by Simon Köhler
 * https://kohlercode.com
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
