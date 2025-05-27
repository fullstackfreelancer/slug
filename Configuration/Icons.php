<?php
/**
 * Icon definitions for the Slug extension backend modules.
 *
 * This configuration array maps icon identifiers to their providers and SVG source files.
 * It registers icons used in the TYPO3 backend module category and individual modules
 * of the Slug extension.
 *
 * Each icon entry specifies:
 * - the icon provider class responsible for rendering the icon,
 * - the source path to the SVG file within the extension resources.
 *
 * @return array<string, array<string, string>>
 */
return [
    'module-category-slug' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:slug/Resources/Public/Icons/slug-be-module-category.svg',
    ],
    'module-slug' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:slug/Resources/Public/Icons/slug-be-module.svg',
    ],
];
