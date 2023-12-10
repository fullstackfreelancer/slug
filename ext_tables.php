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

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('slug', 'Configuration/TypoScript', 'Slug');
    }
);
