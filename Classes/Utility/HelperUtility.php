<?php
namespace SIMONKOEHLER\Slug\Utility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\DataHandling\Model\RecordState;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;

class HelperUtility {


    public function createViewAndTemplatePaths($templateName,$request){
        $viewFactory = GeneralUtility::makeInstance(ViewFactoryInterface::class);
        $viewFactoryData = new ViewFactoryData(
            templateRootPaths: ['EXT:slug/Resources/Private/Templates','EXT:slugpro/Resources/Private/Templates'],
            partialRootPaths: ['EXT:slug/Resources/Private/Partials','EXT:slugpro/Resources/Private/Partials'],
            layoutRootPaths: ['EXT:slug/Resources/Private/Layouts','EXT:slugpro/Resources/Private/Layouts'],
            request: $request,
        );
        $view = $viewFactory->create($viewFactoryData);
        $view->setTemplate($templateName);
        return $view;
    }

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
    public function getSiteByPageUid($pageUid){
        $sitefinder = GeneralUtility::makeInstance(SiteFinder::class);
        try {
            $site = $sitefinder->getSiteByPageId($pageUid);
            $output = $site->getConfiguration();
        }
        catch (SiteNotFoundException $e) {
           $output = false;
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
            ->executeQuery();
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
        $queryBuilder->getRestrictions()->removeAll();
        $statement = $queryBuilder
            ->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid,Connection::PARAM_INT))
            )
            ->executeQuery();
        $output = array();
        while ($row = $statement->fetchAssociative()) {
            $output = $row;
            break;
        }
        return $output;
    }

    public function generatePageSlug($pageUid,$fieldConfig){
        $slugHelper = GeneralUtility::makeInstance(SlugHelper::class, 'pages', 'slug', $fieldConfig);
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $statement = $queryBuilder
            ->select('*')
            ->from('pages')
            ->setMaxResults(1)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pageUid,Connection::PARAM_INT))
            )
            ->executeQuery();
        $record = $statement->fetchAssociative();
        $slugGenerated = $slugHelper->generate($record, $record['pid']);
        return $slugGenerated;
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
           ->executeQuery()
           ->fetchOne();
        return $count;
    }

}
