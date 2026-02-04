<?php
/**
 * @author Simon Kohler <simon@kohlercode.com>
 */

declare(strict_types=1);
namespace KOHLERCODE\Slug\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use KOHLERCODE\Slug\Utility\HelperUtility;
use KOHLERCODE\Slug\Domain\Repository\PageRepository;
use KOHLERCODE\Slug\Domain\Repository\RecordRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class AjaxController
 *
 * Handles various AJAX requests related to slug generation and management.
 */

class AjaxController extends ActionController {

    /**
     * @var HelperUtility
     */
    public $helper;

    /**
     * AjaxController constructor.
     *
     * @param PageRepository $pageRepository
     * @param RecordRepository $recordRepository
     * @param ViewFactoryInterface $viewFactory
     */
    public function __construct(
        private PageRepository $pageRepository,
        private RecordRepository $recordRepository,
        private ViewFactoryInterface $viewFactory
    ){
        $this->helper = GeneralUtility::makeInstance(HelperUtility::class);
    }

    /**
     * Returns a list of pages via AJAX.
     *
     * @param ServerRequestInterface $request
     * @return HtmlResponse
     */
    public function listAction(ServerRequestInterface $request){
        $params = $request->getQueryParams();
        $pages = $this->pageRepository->getPageDataForList(
            $params['maxentries'],
            $params['key'],
            $params['orderby'],
            $params['order'],
            $params['status']
        );
        $view = $this->helper->createViewAndTemplatePaths('ListAjax',$request);
        $view->assign('pages', $pages);
        $view->assign('params', $params);
        $viewRendered = $view->render('ListAjax');
        return new HtmlResponse($viewRendered);
    }

    /**
     * Returns a list of extbase records via AJAX.
     *
     * @param ServerRequestInterface $request
     * @return HtmlResponse
     */
    public function recordAction(ServerRequestInterface $request){
        $params = $request->getQueryParams();
        $records = $this->recordRepository->getRecordDataForList(
            $params['table'],
            $params['slug'],
            $params['title'],
            $params['maxentries'],
            $params['key'],
            $params['orderby'],
            $params['order'],
            $params['status']
        );
        $view = $this->helper->createViewAndTemplatePaths('RecordsAjax',$request);
        $view->assign('records', $records);
        $view->assign('params', $params);
        $viewRendered = $view->render('RecordsAjax');
        return new HtmlResponse($viewRendered);
    }

    /**
     * Updates a page's slug and returns a JSON status response.
     *
     * @param ServerRequestInterface $request
     * @return JsonResponse
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
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($queryParams['uid'],Connection::PARAM_INT))
            )
            ->set('slug',$slug)
            ->executeQuery();
        $responseInfo = [
            'status' => $statement ? '1' : '0',
            'slug' => $slug
        ];
        return new JsonResponse($responseInfo);
    }

    /**
     * Saves a slug to a custom record table.
     *
     * @param ServerRequestInterface $request
     * @return JsonResponse
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
            ->executeQuery();
        $responseInfo['status'] = $statement;
        $responseInfo['slug'] = $slug;
        return new JsonResponse($responseInfo);
    }

    /**
     * Checks if a slug already exists in the pages table.
     *
     * @param ServerRequestInterface $request
     * @return HtmlResponse
     */
    public function slugExists(\Psr\Http\Message\ServerRequestInterface $request)
    {
        $params = $request->getQueryParams();
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $result = $queryBuilder
            ->count('slug')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter($params['slug']))
            )
            ->executeQuery()
            ->fetchColumn(0);
        return new HtmlResponse($result);
    }

    /**
     * Generates a page slug using TYPO3's slug configuration.
     *
     * @param ServerRequestInterface $request
     * @return JsonResponse
     */
    public function getPageSlug(\Psr\Http\Message\ServerRequestInterface $request)
    {
        $fieldConfig = $GLOBALS['TCA']['pages']['columns']['slug']['config'];
        $this->helper = GeneralUtility::makeInstance(HelperUtility::class);
        $queryParams = $request->getQueryParams();
        $slug = $this->helper->generatePageSlug($queryParams['uid'],$fieldConfig);
        $responseInfo['slug'] = $slug;
        return new JsonResponse($responseInfo);
    }

    /**
     * Generates a record slug using a SlugHelper.
     *
     * @param ServerRequestInterface $request
     * @return JsonResponse
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
            ->executeQuery();
        while ($row = $statement->fetch()) {
            $slugGenerated = $slugHelper->sanitize($row[$titleField]);
            break;
        }

        $responseInfo['status'] = $statement;
        $responseInfo['slug'] = $slugGenerated;
        return new JsonResponse($responseInfo);
    }

    /**
     * Renders detailed information about a page's slug and related metadata.
     *
     * @param ServerRequestInterface $request
     * @return HtmlResponse
     */
    public function slugInfo(\Psr\Http\Message\ServerRequestInterface $request)
    {
        $queryParams = $request->getQueryParams();
        $this->helper = GeneralUtility::makeInstance(HelperUtility::class);
        $root = BackendUtility::getRecord('pages',$queryParams['uid']);
        $languages = $this->helper->getLanguages();
        $extensionUtility= GeneralUtility::makeInstance(ExtensionManagementUtility::class);
        if($extensionUtility::isLoaded('slugpro')){
            $slugpro = [
                'version' => '1.0.0'
            ];
        }
        else{
            $slugpro = FALSE;
        }
        $view = $this->helper->createViewAndTemplatePaths('SlugInfo',$request);
        $view->assign('uid',$root['uid']);
        $view->assign('url',$this->helper->getAbsoluteUrl($root['uid']));
        $view->assign('title',$root['seo_title'] ?: $root['title']);
        $view->assign('description',$root['description'] ?: 'n/a');
        $view->assign('slugpro',$slugpro);
        $view->assign('favicon','<img src="https://bearing-sale.com/favicon.ico">');
        $view->assign('sitename','kohlercode.com');
        $translation = [
            'pro' => [
                'feature_note' => $this->helper->getLangKey('pro.feature_note')
            ]
        ];
        $view->assign('translate',$translation);
        return new HtmlResponse($view->render('SlugInfo'));
    }

    /**
     * Updates the page title field via repository.
     *
     * @param ServerRequestInterface $request
     * @return JsonResponse
     */
    public function updatePageTitle(\Psr\Http\Message\ServerRequestInterface $request){
        $params = $request->getQueryParams();
        if(isset($params['title']) && isset($params['uid'])){
            $this->pageRepository->updatePageTitle($params['title'],$params['uid']);
            $responseInfo['status'] = 'success';
        }
        else{
            $responseInfo['status'] = 'error';
        }
        return new JsonResponse($responseInfo);
    }

    /**
     * Updates a custom record title field.
     *
     * @param ServerRequestInterface $request
     * @return JsonResponse
     */
    public function updateRecordTitle(\Psr\Http\Message\ServerRequestInterface $request){
        $params = $request->getQueryParams();
        if(isset($params['title']) && isset($params['uid']) && isset($params['table'])){
            $this->recordRepository->updateRecordTitle($params['title'], $params['uid'], $params['table']);
            $responseInfo['status'] = 'success';
        }
        else{
            $responseInfo['status'] = 'error';
        }
        return new JsonResponse($responseInfo);
    }

}
