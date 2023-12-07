<?php
namespace SIMONKOEHLER\Slug\Utility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\DataHandling\Model\RecordState;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;

/*
 * This file was created by Simon Köhler
 * https://simon-koehler.com
 */

class HelperUtility {


    // Get Extension Manager configuration from the ext_emconf.php of any extension
    public function getEmConfiguration($extKey) {

        $fileName = 'EXT:'.$extKey.'/ext_emconf.php';
        $filePath = GeneralUtility::getFileAbsFileName($fileName);

        if(file_exists($filePath)){
            include $filePath;
            return $EM_CONF[$extKey];
        }
        else{
            return false;
        }

    }

    // Finds and returns the base URL of the website
    public function getSitePrefix($pageData){

        $output = '';
        $sitefinder = GeneralUtility::makeInstance(SiteFinder::class);

        try {
            $site = $sitefinder->getSiteByPageId($pageData['uid']);
            $siteConf = $site->getConfiguration();

            $output = $siteConf['base'];

            // Remove slash from base URL if neccessary
            if(substr($siteConf['base'], -1) === "/"){
                $output = substr($siteConf['base'], 0, -1);
            }
            else{
                $output = $siteConf['base'];
            }

            if($row['isocode']){
                $output = $output.'/'.$pageData['isocode'];
            }
        }
        catch (SiteNotFoundException $e) {
           $output = '[no site]';
        }

        return $output;
    }

    // Finds and returns the base URL of the website
    public function getSiteByPageUid($pageUid){
        $sitefinder = GeneralUtility::makeInstance(SiteFinder::class);
        try {
            $site = $sitefinder->getSiteByPageId($pageUid);
            $output = $site->getConfiguration();
        }
        catch (SiteNotFoundException $e) {
           $output = '[no site]';
        }
        return $output;
    }

    // Gets the correct flag icon for any given language uid
    public function getFlagIconByLanguageUid($sys_language_uid) {
        foreach ($this->getLanguages() as $value) {
            if($value['uid'] === $sys_language_uid){
                $path = '/typo3/sysext/core/Resources/Public/Icons/Flags/'.strtoupper($value['flag']).'.png';
                break;
            }
            else{
                $path = 'typo3conf/ext/slug/Resources/Public/Icons/Flags/default.png';
                break;
            }
        }
        return '<img src="'.$path.'">';
    }


    // Get all languages
    public function getLanguages(){
        // $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_language');
        // $statement = $queryBuilder
        //     ->select('*')
        //     ->from('sys_language')
        //     ->execute();
        // $output = array();
        // while ($row = $statement->fetch()) {
        //     array_push($output, $row);
        // }
        $output = [['uid'=>1,'isocode'=>'de']];
        return $output;
    }


    public function getIsoCodeByLanguageUid($sys_language_uid) {
        foreach ($this->getLanguages() as $value) {
            if($value['uid'] === $sys_language_uid){
                $output = $value['language_isocode'];
                break;
            }
            elseif($sys_language_uid === 0){
                $output = '';
                break;
            }
        }
        return $output;
    }


    public function getPageTranslationsByUid($uid){
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $statement = $queryBuilder
            ->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('l10n_parent', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
             )
            ->execute();
        $output = array();
        while ($row = $statement->fetch()) {
            array_push($output, $row);
        }
        return $output;
    }


    public function getLangKey($key) {
        return \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key,'slug');
    }


    public function getRecordForSlugBuilding($uid,$table){
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $statement = $queryBuilder
            ->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid,\PDO::PARAM_INT))
            )
            ->execute();
        $output = array();
        while ($row = $statement->fetch()) {
            $output = $row;
            break;
        }
        return $output;
    }


    public function returnUniqueSlug($type,$slug,$recordUid,$table,$slugField) {

        switch ($type) {
            case 'page':
                $fieldConfig = $GLOBALS['TCA']['pages']['columns']['slug']['config'];
                $slugHelper = GeneralUtility::makeInstance(SlugHelper::class, 'pages', 'slug', $fieldConfig);
                $record = $this->getRecordForSlugBuilding($recordUid, 'pages');
                $state = RecordStateFactory::forName('pages')->fromArray($record, $record['pid'], $recordUid);
                $uniqueSlug = $slugHelper->buildSlugForUniqueInSite($slug, $state);
                break;
            case 'news':
                $fieldConfig = $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['path_segment']['config'];
                $slugHelper = GeneralUtility::makeInstance(SlugHelper::class, 'tx_news_domain_model_news', 'path_segment', $fieldConfig);
                $record = $this->getRecordForSlugBuilding($recordUid, 'tx_news_domain_model_news');
                $state = RecordStateFactory::forName('tx_news_domain_model_news')->fromArray($record, $record['pid'], $recordUid);
                $uniqueSlug = $slugHelper->buildSlugForUniqueInSite($slug, $state);
                break;
            case 'record':
                $fieldConfig = $GLOBALS['TCA'][$table]['columns'][$slugField]['config'];
                $slugHelper = GeneralUtility::makeInstance(SlugHelper::class, $table, $slugField, $fieldConfig);
                $record = $this->getRecordForSlugBuilding($recordUid, $table);
                $state = RecordStateFactory::forName($table)->fromArray($record, $record['pid'], $recordUid);
                $uniqueSlug = $slugHelper->buildSlugForUniqueInSite($slug, $state);
                break;
            default:
                $uniqueSlug = 'url-'.time();
                break;
        }

        return $uniqueSlug;

    }

    /**
     * function getTotalRecords
     *
     * @return void
     */
    public function getTotalRecords($table){
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $count = $queryBuilder
           ->count('uid')
           ->from($table)
           ->execute()
           ->fetchColumn(0);
        return $count;
    }

}
