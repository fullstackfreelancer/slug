<?php
namespace KOHLERCODE\Slug\Utility;
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
        return $viewFactory->create($viewFactoryData);
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

    /**
     * Builds the URL by determining if the language base is a domain override.
     */
    public function getPageUrl(string $base, string $baseLanguage, string $slug): string
    {
        // 1. Sanitize the slug (remove leading slash)
        $slug = ltrim($slug, '/');

        // 2. Logic for domain replacement or path concatenation
        if (str_starts_with($baseLanguage, 'http')) {
            // If it's a URL and different from the main base, it becomes the new base
            $finalBase = rtrim($baseLanguage, '/');
        } else {
            // Otherwise, combine main base and the language segment (e.g., /es/)
            $mainBase = rtrim($base, '/');
            $langPath = trim($baseLanguage, '/');
            
            // Use array_filter to ignore the language segment if it's empty
            $finalBase = implode('/', array_filter([$mainBase, $langPath]));
        }

        // 3. Return the final joined string
        return $finalBase . '/' . $slug;
    }

    /**
     * Generates an absolute frontend URL for a given page UID (can be a translation UID).
     * * @param int $pageUid The UID of the page or the translation
     * @return string The absolute URL
     */
    public function getAbsoluteUrl(int $pageUid): string
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);

        try {
            // 1. Find the site and the specific language for this UID
            // getSiteByPageId is smart: if you pass a translation UID, 
            // it finds the correct site and language.
            $site = $siteFinder->getSiteByPageId($pageUid);
            
            // 2. We need to find which language this specific UID belongs to.
            // If it's a translation, we need to pass that language ID to the router.
            $pageRecord = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Domain\Repository\PageRepository::class)
                ->getPage($pageUid);
                
            $languageId = (int)($pageRecord['sys_language_uid'] ?? 0);

            // 3. Generate the URI
            $uri = $site->getRouter()->generateUri(
                $pageUid,
                ['_language' => $languageId]
            );

            // Ensure it's absolute (important for cross-domain or backend context)
            return (string)$uri;

        } catch (\Exception $e) {
            // Fallback or log error if page doesn't exist/isn't in a site tree
            return '';
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
