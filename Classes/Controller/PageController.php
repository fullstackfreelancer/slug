<?php
namespace SIMONKOEHLER\Slug\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SIMONKOEHLER\Slug\Utility\HelperUtility;
use SIMONKOEHLER\Slug\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Backend\Tree\View\PageTreeView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use \TYPO3\CMS\Backend\Template\Components\DocHeaderComponent;

/**
 * Controller for managing page-related backend actions in the Slug extension.
 *
 * Provides methods to render page views and listings,
 * handles backend configuration and template initialization.
 */
class PageController extends ActionController {

    /**
     * Current page ID from request.
     *
     * @var int
     */
    protected int $id;

    /**
     * PageRenderer instance.
     *
     * @var \TYPO3\CMS\Core\Page\PageRenderer
     */
    protected $pageRenderer;

    /**
     * IconFactory instance for icon rendering.
     *
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * Helper utility instance.
     *
     * @var HelperUtility
     */
    protected $helper;

    /**
     * Array of available languages.
     *
     * @var array
     */
    protected $languages;

    /**
     * Array of site instances.
     *
     * @var array
     */
    protected $sites;

    /**
     * Backend extension configuration.
     *
     * @var array
     */
    protected $backendConfiguration;

    /**
     * Module template instance for backend rendering.
     *
     * @var ModuleTemplate|null
     */
    protected ?ModuleTemplate $moduleTemplate = null;

    /**
     * Constructor.
     *
     * @param ModuleTemplateFactory $moduleTemplateFactory Factory to create backend module templates.
     * @param PageRepository $pageRepository Repository for fetching page data.
     */
    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        private PageRepository $pageRepository,
    )
    {
         $this->backendConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('slug');
         $this->sites = GeneralUtility::makeInstance(SiteFinder::class)->getAllSites();
    }

    /**
     * Initialization routine before each action.
     *
     * Initializes icon factory, helper utility and module template.
     *
     * @return void
     */
    protected function initializeAction():void
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->helper = GeneralUtility::makeInstance(HelperUtility::class);
        //$this->id = (int)($this->request->getQueryParams()['id'] ?? 0);
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
    }

    /**
     * Default rendering method.
     *
     * @return ResponseInterface Rendered response for the 'Page' template.
     */
    protected function defaultRendering(): ResponseInterface
    {
        return $this->moduleTemplate->renderResponse('Page');
    }

    /**
     * Initializes the backend module template for a given request.
     *
     * Sets flash message queue and title.
     *
     * @param ServerRequestInterface $request Incoming PSR-7 request object.
     * @return ModuleTemplate Initialized module template instance.
     */
    protected function initializeModuleTemplate(
        ServerRequestInterface $request,
    ): ModuleTemplate {
        $view = $this->moduleTemplateFactory->create($request);

        $context = '';
        $view->setFlashMessageQueue($this->getFlashMessageQueue());
        $view->setTitle(
            'SLUG',
            $context,
        );

        return $view;
    }

    /**
     * Action to display a single page and its translated children.
     *
     * Fetches page data by ID and passes it to the view.
     *
     * @return ResponseInterface Rendered response for the 'Page' template.
     */
    protected function pageAction(): ResponseInterface
    {
        $pageUid = $this->request->getQueryParams()['id'];
        $pageData = $this->pageRepository->getPageDataAndTranslatedChildren($pageUid);
        $view = $this->initializeModuleTemplate($this->request);
        $view->assignMultiple([
            'backendConfiguration' => $this->backendConfiguration,
            'beLanguage' => $GLOBALS['BE_USER']->user['lang'],
            'extEmconf' => $this->helper->getEmConfiguration('slug'),
            'page' => $pageData
        ]);
        return $view->renderResponse('Page');
    }

    /**
     * Generates the list view of pages with filtering options.
     *
     * Provides various filter options, assigns necessary variables to the view,
     * and renders the 'List' template.
     *
     * @return ResponseInterface Rendered response for the 'List' template.
     */
    protected function listAction(): ResponseInterface
    {

        $filterOptions['orderby'] = [
            ['value' => 'crdate', 'label' => $this->helper->getLangKey('filter.form.select.option.creation_date')],
            ['value' => 'tstamp', 'label' => $this->helper->getLangKey('filter.form.select.option.tstamp')],
            ['value' => 'title', 'label' => $this->helper->getLangKey('filter.form.select.option.title')],
            ['value' => 'slug', 'label' => $this->helper->getLangKey('filter.form.select.option.slug')],
            ['value' => 'sys_language_uid', 'label' => $this->helper->getLangKey('filter.form.select.option.sys_language_uid')],
            ['value' => 'is_siteroot', 'label' => $this->helper->getLangKey('filter.form.select.option.is_siteroot')],
            ['value' => 'doktype', 'label' => $this->helper->getLangKey('filter.form.select.option.doktype')]
        ];

        $filterOptions['order'] = [
            ['value' => 'DESC', 'label' => $this->helper->getLangKey('filter.form.select.option.descending')],
            ['value' => 'ASC', 'label' => $this->helper->getLangKey('filter.form.select.option.ascending')]
        ];

        $filterOptions['maxentries'] = [
            ['value' => '5', 'label' => '5'],
            ['value' => '10', 'label' => '10'],
            ['value' => '20', 'label' => '20'],
            ['value' => '30', 'label' => '30'],
            ['value' => '40', 'label' => '40'],
            ['value' => '50', 'label' => '50'],
            ['value' => '60', 'label' => '60'],
            ['value' => '70', 'label' => '70'],
            ['value' => '80', 'label' => '80'],
            ['value' => '90', 'label' => '90'],
            ['value' => '100', 'label' => '100'],
            ['value' => '150', 'label' => '150'],
            ['value' => '200', 'label' => '200'],
            ['value' => '300', 'label' => '300'],
            ['value' => '400', 'label' => '400'],
            ['value' => '500', 'label' => '500'],
            ['value' => '1000', 'label' => '1000'],
            ['value' => '1500', 'label' => '1500'],
            ['value' => '2000', 'label' => '2000'],
            ['value' => '3000', 'label' => '3000'],
            ['value' => '4000', 'label' => '4000'],
            ['value' => '5000', 'label' => '5000']
        ];

        // Check if slugpro is loaded
        if(ExtensionManagementUtility::isLoaded('slugpro')){
            $slugpro = $this->helper->getEmConfiguration('slugpro');
        }
        else{
            $slugpro = FALSE;
        }

        $view = $this->initializeModuleTemplate($this->request);

        //Assign variables to the view
        $view->assignMultiple([
            'backendConfiguration' => $this->backendConfiguration,
            'beLanguage' => $GLOBALS['BE_USER']->user['lang'],
            'extEmconf' => $this->helper->getEmConfiguration('slug'),
            'filterOptions' => $filterOptions,
            'sites' => (array) $this->sites,
            'languages' => $this->helper->getLanguages(),
            'slugpro' => $slugpro,
            'additionalTables' => $this->settings['additionalTables'] ? $this->settings['additionalTables'] : [],
            'totalPages' => $this->pageRepository->findTotalPages(),
            'pages' => $this->pageRepository->getPageDataForList(10,'','crdate','ASC'),
            'request' => $this->request->getArguments()
        ]);

        return $view->renderResponse('List');
    }

}
