<?php
namespace KOHLERCODE\Slug\Domain\Repository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use KOHLERCODE\Slug\Utility\HelperUtility;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Repository for managing page records with extended slug and language features.
 *
 * Provides methods to retrieve page data, handle translations, and manage slug states.
 *
 * @package KOHLERCODE\Slug\Domain\Repository
 */
class PageRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

    protected $table = 'pages';
    protected $fieldName = 'slug';
    protected $languages;
    protected $sites;
    protected $helper;
    public $tree;

    /**
     * Constructor initializes sites and helper utility instances.
     */
    public function __construct()
    {
        $this->sites = GeneralUtility::makeInstance(SiteFinder::class)->getAllSites();
        $this->helper = GeneralUtility::makeInstance(HelperUtility::class);
    }

    /**
     * Returns the value of a language field by UID.
     *
     * If no languages are set, assumes default language.
     *
     * @param string $field Field name to retrieve from language record.
     * @param int $uid Language UID to search for.
     * @return string
     */
    public function getLanguageValue($field,$uid){
        $output = '';
        if (!empty($this->languages)) {
            foreach ($this->languages as $language) {
                if($language['uid'] === $uid){
                    $output = $language[$field];
                    break;
                }
            }
        } else {
            /**
            * if we dont have additional languages
            * lets assume we are on the default language
            */
            if($field === 'flag'){
                $output = 'multiple';
            }
        }
        return $output;
    }

    public function findTotalPages(){
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $result = $queryBuilder
            ->count('uid')
            ->from('pages')
            ->where('deleted = 0')
            ->executeQuery()
            ->fetchOne();
        return $result;
    }

    public function getPageData($pageUid){
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $result = $queryBuilder
            ->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pageUid))
            )
            ->executeQuery();
        $row = $result->fetchAssociative();
        return $row;
    }

    /**
     * function getPageDataForList
     */
    public function getPageDataForList($maxitems = 10, $searchkey = '', $orderby = 'crdate', $order = 'ASC', $status = 'all')
    {

        $helper = GeneralUtility::makeInstance(HelperUtility::class);
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $query = $queryBuilder
            ->select(
                'p.uid AS uid',
                'p.title AS title',
                'p.nav_title AS nav_title',
                'p.slug AS slug',
                'p.sys_language_uid AS sys_language_uid',
                'p.l10n_parent AS l10n_parent',
                'p.crdate AS crdate',
                'p.deleted AS deleted',
                'p.hidden AS hidden',
                'p.doktype AS doktype',
                'p.tstamp AS tstamp',
                'p.seo_title AS seo_title',
                'p.is_siteroot AS is_siteroot',
                'p.tx_slug_locked AS tx_slug_locked',
                't.uid AS t_uid',
                't.title AS t_title',
                't.nav_title AS t_nav_title',
                't.slug AS t_slug',
                't.tx_slug_locked AS t_tx_slug_locked',
                't.sys_language_uid AS t_sys_language_uid'
            )
            ->from('pages', 'p')
            ->leftJoin(
                'p',
                'pages',
                't',
                $queryBuilder->expr()->and(
                    $queryBuilder->expr()->eq('t.l10n_parent', $queryBuilder->quoteIdentifier('p.uid')),
                    $queryBuilder->expr()->gt('t.sys_language_uid', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
                )
            )
            ->where(
                $queryBuilder->expr()->eq('p.sys_language_uid', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
            )
            ->setMaxResults($maxitems)
            ->orderBy($orderby ?: 'p.crdate', $order ?: 'DESC');


            if (isset($searchkey)) {
                $query->andWhere(
                    $queryBuilder->expr()->like(
                        'p.slug',
                        $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($searchkey) . '%')
                    )
                );
                $query->orWhere(
                    $queryBuilder->expr()->like(
                        't.slug',
                        $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($searchkey) . '%')
                    )
                );
            }

            if ($status !== 'all') {
                if ($status === 'visible') {
                    $query->andWhere($queryBuilder->expr()->eq('p.deleted', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)));
                    $query->andWhere($queryBuilder->expr()->eq('p.hidden', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)));
                }
                if ($status === 'hidden') {
                    $query->andWhere($queryBuilder->expr()->eq('p.hidden', $queryBuilder->createNamedParameter(1, Connection::PARAM_INT)));
                    $query->andWhere($queryBuilder->expr()->eq('p.deleted', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)));
                }
            }
            else{
                $query->andWhere($queryBuilder->expr()->eq('p.deleted', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)));
            }


        $result = $query->executeQuery();

        $pages = [];

        while ($row = $result->fetchAssociative()) {
            $uid = $row['uid'];

            if (!isset($pages[$uid])) {
                $site = $helper->getSiteByPageUid($uid);

                // Check if page belongs to a site. Deleted pages are NOT assigned to a site
                if($site){
                    $flag = $site['languages'][$row['sys_language_uid']]['flag'];
                    $base = rtrim($site['base'],'/');
                    $base_language = rtrim($site['languages'][$row['sys_language_uid']]['base'],'/');
                }

                $pages[$uid] = [
                    'uid' => $uid,
                    'title' => $row['title'],
                    'doktype' => $row['doktype'],
                    'is_siteroot' => $row['is_siteroot'],
                    'nav_title' => $row['nav_title'],
                    'slug' => $row['slug'],
                    'tx_slug_locked' => $row['tx_slug_locked'],
                    'sys_language_uid' => $row['sys_language_uid'],
                    'l10n_parent' => $row['l10n_parent'],
                    'crdate' => $row['crdate'],
                    'deleted' => $row['deleted'],
                    'hidden' => $row['hidden'],
                    'site' => $site,
                    'flag' => $flag ?? '',
                    'translations' => [],
                    'base' => $base ?? '',
                    'base_language' => $base_language ?? '',
                    'full_url' => $helper->getPageUrl($base,$base_language,$row['slug'])
                ];
            }

            if (!empty($row['t_uid'])) {
                $t_base_language = rtrim($site['languages'][$row['t_sys_language_uid']]['base'],'/') ?? '';
                $pages[$uid]['_translations'][$row['t_sys_language_uid']] = [
                    'uid' => $row['t_uid'],
                    'doktype' => $row['doktype'],
                    'title' => $row['t_title'],
                    'nav_title' => $row['t_nav_title'],
                    'slug' => $row['t_slug'],
                    'tx_slug_locked' => $row['t_tx_slug_locked'],
                    'sys_language_uid' => $row['t_sys_language_uid'],
                    'l10n_parent' => $row['l10n_parent'],
                    'flag' => $site['languages'][$row['t_sys_language_uid']]['flag'],
                    'base' => rtrim($site['base'],'/') ?? '',
                    'base_language' => $t_base_language,
                    'full_url' => $helper->getPageUrl($base,$t_base_language,$row['t_slug'])
                ];
            }
        }

        return $pages;
    }

    public function updatePageTitle($title,$pageUid)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder
            ->update('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $pageUid)
            )
            ->set('title', $title)
            ->executeQuery();
    }

    /*
    This function is used to get the data of a single page and its translations based on the page UID. 
    It returns an array with the page data and its translations.
    */
    public function getPageDataAndTranslatedChildren($pageUid)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();

        $queryBuilder
            ->select('*')
            ->from('pages')
            ->orderBy('l10n_parent','ASC')
            ->where(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pageUid, Connection::PARAM_INT)),
                    $queryBuilder->expr()->and(
                        $queryBuilder->expr()->eq('l10n_parent', $queryBuilder->createNamedParameter($pageUid, Connection::PARAM_INT)),
                        $queryBuilder->expr()->gt('l10n_parent', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
                    )
                )
            );

        $stmt = $queryBuilder->executeQuery();
        $pages = $stmt->fetchAllAssociative();

        foreach($pages as $key => $page){
            $site = $this->helper->getSiteByPageUid($page['uid']);
            $pages[$key]['site'] = $site;

            // Default: no language found
            $pages[$key]['language'] = null;

            if (!empty($site['languages']) && isset($page['sys_language_uid'])) {
                foreach ($site['languages'] as $language) {
                    if ((int)$language['languageId'] === (int)$page['sys_language_uid']) {
                        $pages[$key]['language'] = $language;
                        break;
                    }
                }
            }
        }

        return $pages;
    }

}
