<?php
/**
 * Extension configuration for the "Slug" TYPO3 extension.
 *
 * This configuration provides metadata and settings for the extension,
 * including title, description, author information, version, state,
 * dependencies, and autoloading rules.
 *
 * @package    Slug
 * @license    http://opensource.org/licenses/gpl-license.php  GNU Public License
 * @author     Moshe Teutsch <moteutsch@gmail.com>
 */



 /**
 *
 * @var array{
 *   title: string,
 *   description: string,
 *   category: string,
 *   author: string,
 *   author_email: string,
 *   company: string,
 *   state: string,
 *   clearCacheOnLoad: bool,
 *   version: string,
 *   constraints: array{
 *     depends: array<string, string>,
 *     conflicts: array<string, mixed>,
 *     suggests: array<string, mixed>
 *   },
 *   autoload: array{
 *     'psr-4': array<string, string>
 *   }
 * }
 */
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
            'SIMONKOEHLER\\Slug\\' => 'Classes',
        ],
    ],
];
