<?php
defined('TYPO3') || die('Access denied.');

call_user_func(
    function()
    {
        /***************
         * Make the extension configuration accessible
         */
        $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        );
        $slugConfiguration = $extensionConfiguration->get('slug');

        // \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        //     'Slug',
        //     'site',
        //     'slugs',
        //     '',
        //     [
        //         \SIMONKOEHLER\Slug\Controller\PageController::class => 'ajaxList,site,tree',
        //         \SIMONKOEHLER\Slug\Controller\ExtensionController::class => 'additionalTable'
        //     ],
        //     [
        //         'access' => 'user,group',
        //         'icon'   => 'EXT:slug/Resources/Public/Icons/slug-be-module.svg',
        //         'labels' => 'LLL:EXT:slug/Resources/Private/Language/locallang_slugs.xlf',
        //     ]
        // );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('slug', 'Configuration/TypoScript', 'Slug');
    }
);
