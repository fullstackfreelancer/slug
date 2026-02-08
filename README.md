# <img src="https://raw.githubusercontent.com/fullstackfreelancer/slug/refs/heads/master/Resources/Public/Icons/Extension.svg" width="25" height="25"> slug - TYPO3 CMS Backend Module for better SEO

Official Repository of the 'slug' Backend Module for TYPO3 9.5, 10, 11, 12 and soon 13.4.0

The Slug backend module is designed to help manage large amounts of URL-slugs for pages and extension records. Currently, it provides a simple list for pages and custom records, which can be filtered with different parameters. Slugs can be edited and saved quickly and efficiently. The modules regenerate or save **all slugs of the current list view** with just one click. I have tested the functionality with 500+ empty news records so far, without any problem.

## STABLE VERSION COMING SOON

Your ideas to improve and extend the slug module are more than welcome: [send a message](https://kohlercode.com/contact)

[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://paypal.me/fullstackfreelancer/25)

## FEATURES

* Add custom records of your extensions via $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['slug'] (see manual below)
* Quickly edit, save and regenerate slugs for pages and other record types (new in Version 2)
* Mass generation and storage of slugs
* List views filterable with different parameters
* Search engine preview for pages, displays the updated slug as you type
* Uses TYPO3 core slug generation functions

## CHANGELOG

See the changelog for more details:
https://github.com/fullstackfreelancer/slug/blob/master/CHANGELOG.md

## USAGE

### Installation

* For Composer use ```composer require kohlercode/slug```
* Download the latest version here: https://extensions.typo3.org/extension/slug/ or install it with the extension manager of your TYPO3 installation
* No further configuration is required, but you should delete all the backend caches after installation to make sure the extension is working properly.

## HOW TO ENABLE CUSTOM EXTBASE RECORDS

Note: To enable custom records functionality, you must add configuration values to your "additional.php" file in the system settings!

Important to know: Editing the slugs works only if the desired table contains a field for the title and a field for the slug. The names of the fields can be determined by PHP in your "additional.php". But be careful. If you use a wrong field, the slug extension can destroy your data. We take no responsibility for it. So it's best not to test in a live web site before.

Very important to know:
* If you want to use an image symbol, make sure the image exists. The slug extension is currently NOT checking this!
* You can only use tables that are correctly prepared for TYPO3 use
    * The configuration array **$GLOBALS['TCA']['tx_your_table_name']['columns']['your_slug_field']['config']** needs to exist in the TYPO3 system. Otherwise the system will throw errors.
    * The fields **crdate,tstamp,uid** AND your custom fields for the title and the slug need to exist in your table!

Here's an example code you will need to make a custom table work.
```php
# Global extension configuration
<?php
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['slug'] = [
    'settings'=> [
        'defaultSortfield' => 'uid',
        'defaultSortby' => 'ASC',
        'defaultMaxEntries' => 10,
    ],
    'additionalTables' => [
        'tx_news_domain_model_news' => [
            'label' => 'News',
            'slugField' => 'path_segment',
            'titleField' => 'title',
            'pid' => 44,
            'icon' => 'ext-news-type-default' // v14: Use the Icon Identifier!
        ],
    ]
];
```

## KNOWN PROBLEMS

This part will be updated soon, since the extension has been updated recently.

## Want to report an issue?

https://github.com/fullstackfreelancer/slug/issues

## All other requests

**Contact:** https://kohlercode.com/contact