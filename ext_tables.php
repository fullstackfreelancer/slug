<?php
/**
 * Registers the TypoScript static configuration for the "slug" extension
 * and makes the extension configuration accessible.
 *
 * This closure is immediately invoked to:
 * - Instantiate the ExtensionConfiguration service.
 * - Retrieve the configuration for the "slug" extension.
 * - Register the TypoScript static file located at
 *   Configuration/TypoScript for inclusion in site templates.
 *
 * This ensures that the extension's TypoScript configuration is available
 * and can be included easily within TYPO3 backend templates.
 */
 
defined('TYPO3') || die('Access denied.');

call_user_func(
    function()
    {
        /**
         * Make the extension configuration accessible
         */
        $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        );
        $slugConfiguration = $extensionConfiguration->get('slug');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('slug', 'Configuration/TypoScript', 'Slug');
    }
);
