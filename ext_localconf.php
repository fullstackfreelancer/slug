<?php
/**
 * Registers TypoScript constants and setup configuration for the "slug" extension.
 *
 * This script includes external TypoScript files into TYPO3's TypoScript
 * configuration:
 * - constants.typoscript: defines constants for the extension
 * - setup.typoscript: contains the main TypoScript setup for the extension
 *
 * The inclusion is done via ExtensionManagementUtility methods, which ensure
 * the TypoScript is properly loaded during TYPO3 bootstrap.
 */

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptConstants(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:slug/Configuration/TypoScript/constants.typoscript">'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:slug/Configuration/TypoScript/setup.typoscript">'
);
