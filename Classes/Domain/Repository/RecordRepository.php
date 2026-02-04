<?php
namespace KOHLERCODE\Slug\Domain\Repository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use KOHLERCODE\Slug\Utility\HelperUtility;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Repository for managing custom extbase records with extended slug and language features.
 *
 * Provides methods to retrieve page data, handle translations, and manage slug states.
 *
 * @package KOHLERCODE\Slug\Domain\Repository
 */
class RecordRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

    protected $table;
    protected $fieldName;
    protected $languages;
    protected $sites;
    protected $helper;
    public $tree;

    /**
     * Constructor initializes sites and helper utility instances.
     */
    public function __construct()
    {
        $this->helper = GeneralUtility::makeInstance(HelperUtility::class);
    }

    /**
     * Generic function to fetch localized records for any table
     * * @param string $tableName The database table (e.g., 'tx_news_domain_model_news')
     * @param string $slugField The field containing the slug (e.g., 'path_segment')
     * @param string $titleField The field containing the title (e.g., 'title')
     * @param int $maxitems Pagination limit
     * @param string $searchkey Search string for the slug field
     * @param string $orderby Field to sort by
     * @param string $order Direction (ASC/DESC)
     * @return array
     */
    public function getRecordDataForList(
        string $tableName,
        string $slugField = 'slug',
        string $titleField = 'title',
        $maxitems = 10,
        string $searchkey = '',
        string $orderby = 'crdate',
        string $order = 'ASC'
    ): array {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
        $queryBuilder->getRestrictions()->removeAll();

        // 1. Build the dynamic SELECT
        $query = $queryBuilder
            ->select(
                'p.uid AS uid',
                'p.' . $titleField . ' AS title',
                'p.' . $slugField . ' AS slug',
                'p.sys_language_uid AS sys_language_uid',
                'p.l10n_parent AS l10n_parent',
                'p.crdate AS crdate',
                'p.tstamp AS tstamp',
                'p.hidden AS hidden',
                'p.deleted AS deleted',
                't.uid AS t_uid',
                't.' . $titleField . ' AS t_title',
                't.' . $slugField . ' AS t_slug',
                't.sys_language_uid AS t_sys_language_uid'
            )
            ->from($tableName, 'p')
            // Left join for translations (where l10n_parent matches p.uid)
            ->leftJoin(
                'p',
                $tableName,
                't',
                $queryBuilder->expr()->and(
                    $queryBuilder->expr()->eq('t.l10n_parent', $queryBuilder->quoteIdentifier('p.uid')),
                    $queryBuilder->expr()->gt('t.sys_language_uid', $queryBuilder->createNamedParameter(0, \TYPO3\CMS\Core\Database\Connection::PARAM_INT))
                )
            )
            // Only fetch default language records as parents
            ->where(
                $queryBuilder->expr()->eq('p.sys_language_uid', $queryBuilder->createNamedParameter(0, \TYPO3\CMS\Core\Database\Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('p.deleted', $queryBuilder->createNamedParameter(0, \TYPO3\CMS\Core\Database\Connection::PARAM_INT))
            )
            ->setMaxResults($maxitems)
            //->orderBy('p.' . ($orderby ?: 'crdate'), $order ?: 'DESC');
            ->orderBy($orderby ?: 'crdate', $order ?: 'DESC');

        // 2. Dynamic Search
        if (!empty($searchkey)) {
            $searchParam = $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($searchkey) . '%');
            $query->andWhere(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->like('p.' . $slugField, $searchParam),
                    $queryBuilder->expr()->like('t.' . $slugField, $searchParam)
                )
            );
        }

        $result = $query->executeQuery();
        $records = [];

        // 3. Process Result
        while ($row = $result->fetchAssociative()) {
            $uid = $row['uid'];

            if (!isset($records[$uid])) {
                $records[$uid] = [
                    'uid' => $uid,
                    'title' => $row['title'],
                    'slug' => $row['slug'],
                    'sys_language_uid' => $row['sys_language_uid'],
                    'l10n_parent' => $row['l10n_parent'],
                    'crdate' => $row['crdate'],
                    'tstamp' => $row['tstamp'],
                    'hidden' => $row['hidden'],
                    'deleted' => $row['deleted'],
                    'table' => $tableName,
                    'icon' => '<i class="bi-file-earmark-fill"></i>',
                    '_translations' => []
                ];
            }

            if (!empty($row['t_uid'])) {
                $records[$uid]['_translations'][$row['t_sys_language_uid']] = [
                    'uid' => $row['t_uid'],
                    'title' => $row['t_title'],
                    'slug' => $row['t_slug'],
                    'sys_language_uid' => $row['t_sys_language_uid'],
                    'l10n_parent' => $row['l10n_parent'],
                    'icon' => '<i class="bi-file-earmark"></i>',
                    'table' => $tableName,
                ];
            }
        }

        return $records;
    }

    public function updateRecordTitle($title,$uid,$table)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $queryBuilder
            ->update($table)
            ->where(
                $queryBuilder->expr()->eq('uid', $uid)
            )
            ->set('title', $title)
            ->executeQuery();
    }

}
