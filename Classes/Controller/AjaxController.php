<?php
namespace SIMONKOEHLER\Slug\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SIMONKOEHLER\Slug\Utility\HelperUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class AjaxController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

    /**
     * function ajaxList
     */
    public function ajaxList(ServerRequestInterface $request): ResponseInterface
    {

        $helper = GeneralUtility::makeInstance(HelperUtility::class);
        $output = [];
        $queryParams = $request->getQueryParams();

        $currentPage = $queryParams['page'];
        $entriesPerPage = $queryParams['maxentries'];
        $totalRecords = $helper->getTotalRecords($queryParams['table']);

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($queryParams['table']);
        $queryBuilder->getRestrictions()->removeAll();
        $query = $queryBuilder
            ->select('*')
            ->from($queryParams['table'])
            ->setMaxResults($entriesPerPage)
            ->orderBy($queryParams['orderby'] ?: 'crdate',$queryParams['order'] ?: 'DESC');
        if($queryParams['key']){
            $query->where($queryBuilder->expr()->like('slug',$queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($queryParams['key']) . '%')));
        }
        $result = $query->execute();

        while ($row = $result->fetch()) {
            $row['sitePrefix'] = $helper->getSitePrefix($row);
            $row['site'] = $helper->getSiteByPageUid($row['uid']);
            $output[] = $row;
        }
        return new JsonResponse($output);
    }


    /**
     * function savePageSlug
     *
     * @return void
     */
    public function savePageSlug(\Psr\Http\Message\ServerRequestInterface $request)
    {
        $queryParams = $request->getQueryParams();
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $this->helper = GeneralUtility::makeInstance(HelperUtility::class);
        $slug = $this->helper->returnUniqueSlug('page', $queryParams['slug'], $queryParams['uid'], 'pages', 'slug');
        $queryBuilder->getRestrictions()->removeAll();
        $statement = $queryBuilder
            ->update('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($queryParams['uid'],\PDO::PARAM_INT))
            )
            ->set('slug',$slug) // Function "createNamedParameter" is NOT needed here!
            ->execute();

        if($statement){
            $responseInfo['status'] = '1';
            $responseInfo['slug'] = $slug;
        }
        else{
            $responseInfo['status'] = '0';
            $responseInfo['slug'] = $slug;
        }
        return new JsonResponse($responseInfo);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function saveRecordSlug(\Psr\Http\Message\ServerRequestInterface $request)
    {
        $queryParams = $request->getQueryParams();
        $uid = $queryParams['uid'];
        $table = $queryParams['table'];
        $slug = $queryParams['slug'];
        $slugField  = $queryParams['slugField'];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $statement = $queryBuilder
            ->update($table)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid,\PDO::PARAM_INT))
            )
            ->set($slugField,$slug) // Function "createNamedParameter" is NOT needed here!
            ->execute();
        $responseInfo['status'] = $statement;
        $responseInfo['slug'] = $slug;
        return new JsonResponse($responseInfo);
    }

    /**
     * function slugExists
     *
     * @return void
     */
    public function slugExists(\Psr\Http\Message\ServerRequestInterface $request)
    {
        $queryParams = $request->getQueryParams();
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $result = $queryBuilder
            ->count('slug')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter($queryParams['slug']))
            )
            ->execute()
            ->fetchColumn(0);
        return new HtmlResponse($result);
    }

    /**
     * function generatePageSlug
     *
     * @return void
     */
    public function generatePageSlug(\Psr\Http\Message\ServerRequestInterface $request)
    {
        $fieldConfig = $GLOBALS['TCA']['pages']['columns']['slug']['config'];
        $slugHelper = GeneralUtility::makeInstance(SlugHelper::class, 'pages', 'slug', $fieldConfig);
        $this->helper = GeneralUtility::makeInstance(HelperUtility::class);
        $queryParams = $request->getQueryParams();
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $statement = $queryBuilder
            ->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($queryParams['uid'],\PDO::PARAM_INT))
            )
            ->execute();
        while ($row = $statement->fetch()) {
            $slugGenerated = $slugHelper->generate($row, $row['pid']);
            break;
        }
        $slug = $this->helper->returnUniqueSlug('page', $slugGenerated, $row['uid'], 'pages', 'slug');
        $responseInfo['slug'] = $slug;
        return new JsonResponse($responseInfo);
    }

    /**
     * function generateRecordSlug
     *
     * @return void
     */
    public function generateRecordSlug(\Psr\Http\Message\ServerRequestInterface $request)
    {
        $queryParams = $request->getQueryParams();
        $uid = $queryParams['uid'];
        $table = $queryParams['table'];
        $slugField  = $queryParams['slugField'];
        $titleField  = $queryParams['titleField'];

        $fieldConfig = $GLOBALS['TCA'][$table]['columns'][$slugField]['config'];
        $slugHelper = GeneralUtility::makeInstance(SlugHelper::class, $table, $slugField, $fieldConfig);
        $this->helper = GeneralUtility::makeInstance(HelperUtility::class);

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $statement = $queryBuilder
            ->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($queryParams['uid'],\PDO::PARAM_INT))
            )
            ->execute();
        while ($row = $statement->fetch()) {
            $slugGenerated = $slugHelper->sanitize($row[$titleField]);
            break;
        }

        $responseInfo['status'] = $statement;
        $responseInfo['slug'] = $slugGenerated;
        return new JsonResponse($responseInfo);
    }

    /**
     * function loadTreeItemSlugs
     *
     * @return void
     */
    public function loadTreeItemSlugs(\Psr\Http\Message\ServerRequestInterface $request)
    {
        $queryParams = $request->getQueryParams();
        $this->helper = GeneralUtility::makeInstance(HelperUtility::class);
        $translations = $this->helper->getPageTranslationsByUid($queryParams['uid']);
        $root = BackendUtility::getRecord('pages',$queryParams['uid']);
        $languages = $this->helper->getLanguages();
        $html .= '<div class="well">';
        $html .= '<h2>'.$root['title'].' <small>'.$root['seo_title'].'</small></h2>';
        $html .= '<div class="input-group">'
                . '<span class="input-group-addon"><i class="fa fa-globe"></i></span>'
                . '<input type="text" data-uid="'.$root['uid'].'" value="'.$root['slug'].'" class="form-control slug-input page-'.$root['uid'].'">'
                . '<span class="input-group-btn"><button data-uid="'.$root['uid'].'" id="savePageSlug-'.$root['uid'].'" class="btn btn-default savePageSlug" title="Save slug"><i class="fa fa-save"></i></button></span>'
                . '</div>';
        foreach ($translations as $page) {
            foreach ($languages as $value) {
                if($value['uid'] === $page['sys_language_uid']){
                    $icon = $value['language_isocode'];
                }
            }
            $html .= '<h3>'.$page['title'].' <small>'.$page['seo_title'].'</small></h3>';
            $html .= '<div class="input-group">'
                . '<span class="input-group-addon">'.$icon.'</span>'
                . '<input type="text" data-uid="'.$page['uid'].'" value="'.$page['slug'].'" class="form-control slug-input page-'.$page['uid'].'">'
                . '<span class="input-group-btn"><button data-uid="'.$page['uid'].'" id="savePageSlug-'.$page['uid'].'" class="btn btn-default savePageSlug" title="Save slug"><i class="fa fa-save"></i></button></span>'
                . '</div>';
        }
        $html .= '</div>';
        return new HtmlResponse($html);
    }

    /**
     * function slugInfo
     *
     * @return void
     */
    public function slugInfo(\Psr\Http\Message\ServerRequestInterface $request)
    {
        $queryParams = $request->getQueryParams();
        $this->helper = GeneralUtility::makeInstance(HelperUtility::class);
        $translations = $this->helper->getPageTranslationsByUid($queryParams['uid']);
        $root = BackendUtility::getRecord('pages',$queryParams['uid']);
        $languages = $this->helper->getLanguages();

        $extensionUtility= GeneralUtility::makeInstance(ExtensionManagementUtility::class);
        // Check if slugpro is loaded
        if($extensionUtility::isLoaded('slugpro')){
            $slugpro = [
                'version' => '1.0.0'
            ];
        }
        else{
            $slugpro = FALSE;
        }

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setLayoutRootPaths(array(
            GeneralUtility::getFileAbsFileName('EXT:slug/Resources/Private/Layouts'),
            GeneralUtility::getFileAbsFileName('EXT:slugpro/Resources/Private/Layouts')
        ));
        $view->setTemplateRootPaths(array(
            GeneralUtility::getFileAbsFileName('EXT:slug/Resources/Private/Templates'),
            GeneralUtility::getFileAbsFileName('EXT:slugpro/Resources/Private/Templates')
        ));
        $view->setPartialRootPaths(array(
            GeneralUtility::getFileAbsFileName('EXT:slug/Resources/Private/Partials'),
            GeneralUtility::getFileAbsFileName('EXT:slugpro/Resources/Private/Partials')
        ));
        $view->setTemplate('SlugInfo');
        $view->assign('uid',$root['uid']);
        $view->assign('url',$root['slug']);
        $view->assign('title',$root['seo_title'] ?: $root['title']);
        $view->assign('description',$root['description'] ?: 'Best practice is to keep meta description length between 120-150 characters. This ensures your entire description will appear on both desktop and mobile.');
        $view->assign('slugpro',$slugpro);

        $translation = [
            'pro' => [
                'feature_note' => $this->helper->getLangKey('pro.feature_note')
            ]
        ];
        $view->assign('translate',$translation);

        $viewRendered = $view->render();

        return new HtmlResponse($viewRendered);

    }

}
