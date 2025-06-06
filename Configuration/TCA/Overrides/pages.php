<?php
/*
 * TYPO3 TCA Override for the "pages" table
 *
 * Adds a custom checkbox field "tx_slug_locked" to control slug locking per page.
 * Also extends the existing "slug" field configuration to increase the input size.
 */
 
if (!defined('TYPO3')) {
  die ('Access denied.');
}

$fields = array(
  'tx_slug_locked' => array(
    'label' => 'LLL:EXT:slug/Resources/Private/Language/locallang_db.xlf:tx_slug_domain_model_page.slug_lock',
    'exclude' => 1,
    'config' => array(
        'type' => 'check',
        'renderType' => 'checkboxToggle',
        'items' => [
            [
                0 => '',
                1 => '',
                'labelChecked' => 'Enabled',
                'labelUnchecked' => 'Disabled',
            ]
        ],
    ),
  )
);

// Add new fields to pages:
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $fields);
$GLOBALS['TCA']['pages']['columns']['slug']['config']['size'] = 100;
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('pages', 'title', 'tx_slug_locked', 'after:slug');
