<?php
namespace SIMONKOEHLER\Slug\Controller;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Site\SiteFinder;
use Psr\Http\Message\ResponseInterface;
use SIMONKOEHLER\Slug\Utility\HelperUtility;
use SIMONKOEHLER\Slug\Domain\Repository\ExtensionRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class ExtensionController extends ActionController {

    /**
    * @var ExtensionRepository
    */
    protected $extensionRepository;

    /**
    * @var HelperUtility
    */
    public $helper;
    protected $backendConfiguration;
    protected ?ModuleTemplate $moduleTemplate = null;

    /**
    * @param ModuleTemplateFactory $moduleTemplateFactory
    */
    public function __construct(protected readonly ModuleTemplateFactory $moduleTemplateFactory)
    {
        //$this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        //$this->helper = GeneralUtility::makeInstance(HelperUtility::class);
        $this->backendConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('slug');
        $this->sites = GeneralUtility::makeInstance(SiteFinder::class)->getAllSites();
    }

    /**
     * Injects the Repository
     *
     * @param \SIMONKOEHLER\Slug\Domain\Repository\ExtensionRepository $extensionRepository
     */
    public function injectExtensionRepository(\SIMONKOEHLER\Slug\Domain\Repository\ExtensionRepository $extensionRepository)
    {
        $this->extensionRepository = $extensionRepository;
    }

    protected function initializeAction():void
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->helper = GeneralUtility::makeInstance(HelperUtility::class);
        $this->id = (int)($this->request->getQueryParams()['id'] ?? 0);
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
    }

    protected function defaultRendering(): ResponseInterface
    {
        $this->moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($this->moduleTemplate->renderContent());
    }

    protected function listAction(): ResponseInterface
    {

        $backendConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('slug');

         // Check if filter variables are available, otherwise set default values from ExtensionConfiguration
        if($this->request->hasArgument('filter')){
            $filterVariables = $this->request->getArgument('filter');
        }
        else{
            $filterVariables['maxentries'] = $backendConfiguration['recordMaxEntries'];
            $filterVariables['orderby'] = $backendConfiguration['recordOrderBy'];
            $filterVariables['order'] = $backendConfiguration['recordOrder'];
            $filterVariables['key'] = '';
        }

        $filterOptions['order'] = [
            ['value' => 'DESC', 'label' => $this->helper->getLangKey('filter.form.select.option.descending')],
            ['value' => 'ASC', 'label' => $this->helper->getLangKey('filter.form.select.option.ascending')]
        ];

        $filterOptions['maxentries'] = [
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
            ['value' => '500', 'label' => '500']
        ];

        if($this->request->hasArgument('table')){
            $table = $this->request->getArgument('table');
            if($this->extensionRepository->tableExists($table)){

                // Set the order by options for fluid viewhelper f:form.switch
                $filterOptions['orderby'] = [
                    ['value' => 'crdate', 'label' => $this->helper->getLangKey('filter.form.select.option.creation_date')],
                    ['value' => $this->settings['additionalTables'][$table]['titleField'], 'label' => $this->helper->getLangKey('filter.form.select.option.title')],
                    ['value' => $this->settings['additionalTables'][$table]['slugField'], 'label' => $this->helper->getLangKey('filter.form.select.option.path_segment')],
                    ['value' => 'sys_language_uid', 'label' => $this->helper->getLangKey('filter.form.select.option.sys_language_uid')],
                ];

                $records = $this->extensionRepository->getAdditionalRecords(
                        $table,
                        $filterVariables,
                        $this->settings['additionalTables']
                        );

                $this->view->assignMultiple([
                    'filter' => $filterVariables,
                    'filterOptions' => $filterOptions,
                    'records' => $records,
                    'table' => $table,
                    'slugField' => $this->settings['additionalTables'][$table]['slugField'],
                    'titleField' => $this->settings['additionalTables'][$table]['titleField'],
                    'label' => $this->settings['additionalTables'][$table]['label']
                ]);
            }
            else{
                $this->view->assignMultiple([
                    'message' => "Table doesn't exist!"
                ]);
            }
        }
        else{
            $this->view->assignMultiple([
                'message' => "Table argument not given!"
            ]);
        }

        $this->view->assignMultiple([
            'backendConfiguration' => $backendConfiguration,
            'additionalTables' => $this->settings['additionalTables'],
            'extEmconf' => $this->helper->getEmConfiguration('slug'),
        ]);

        return $this->defaultRendering();

    }

}
