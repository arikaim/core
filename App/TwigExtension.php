<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\App;

use Twig\TwigFunction;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

use Arikaim\Core\View\Html\Page;
use Arikaim\Core\Interfaces\CacheInterface;
use Arikaim\Core\Interfaces\Access\AuthInterface;
use Arikaim\Core\Interfaces\OptionsInterface;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Access\Csrf;
use Arikaim\Core\System\System;
use Arikaim\Core\System\Composer;
use Arikaim\Core\System\Update;
use Arikaim\Core\App\Install;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Http\Url;
use Arikaim\Core\Http\Session;
use Arikaim\Core\App\ArikaimStore;
use Arikaim\Core\Routes\Route;
use Arikaim\Core\Db\Schema;

/**
 *  Template engine functions, filters and tests.
 */
class TwigExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * Model classes requires control panel access 
     *
     * @var array
     */
    protected $protectedModels = [
        'PermissionRelations',
        'Permissions',
        'Routes',
        'Modules',
        'Events',
        'Drivers',
        'Extensions',
        'Jobs',
        'EventSubscribers'
    ];

    /**
     * Protected services requires control panel access  
     *
     * @var array
     */
    protected $protectedServices = [
        'config',       
        'packages'
    ];

    /**
     * Protected services requires logged user
     *
     * @var array
     */
    protected $userProtectedServices = [      
        'storage'      
    ];

    /**
     * Cache
     *
     * @var CacheInterface
     */
    protected $cache;

    /**
     * Access
     *
     * @var AuthInterface
     */
    protected $access;

    /**
     * options
     *
     * @var OptionsInterface
     */
    protected $options;

    /**
     * Constructor
     *
     * @param CacheInterface $cache
     * @param AuthInterface $access
     */
    public function __construct(CacheInterface $cache, AuthInterface $access, OptionsInterface $options)
    {
        $this->cache = $cache;
        $this->access = $access;
        $this->options = $options;
    }

    /**
     * Rempate engine global variables
     *
     * @return array
     */
    public function getGlobals() 
    {
        return [
            'system_template_name'  => Page::SYSTEM_TEMPLATE_NAME,
            'domain'                => (\defined('DOMAIN') == true) ? DOMAIN : null,
            'base_url'              => Url::BASE_URL,     
            'DIRECTORY_SEPARATOR'   => DIRECTORY_SEPARATOR
        ];
    }

    /**
     * Template engine functions
     *
     * @return array
     */
    public function getFunctions() 
    {
        $items = $this->cache->fetch('arikaim.twig.functions');
        if (\is_array($items) == true) {
            return $items;
        }

        $items = [
            new TwigFunction('isMobile',['Arikaim\\Core\\Utils\\Mobile','mobile']),
            // paginator
            new TwigFunction('paginate',['Arikaim\\Core\\Paginator\\SessionPaginator','create']),
            new TwigFunction('paginatorUrl',[$this,'getPaginatorUrl']),
            new TwigFunction('clearPaginator',['Arikaim\\Core\\Paginator\\SessionPaginator','clearPaginator']),            
            new TwigFunction('getPaginator',['Arikaim\\Core\\Paginator\\SessionPaginator','getPaginator']),
            new TwigFunction('getRowsPerPage',['Arikaim\\Core\\Paginator\\SessionPaginator','getRowsPerPage']),
            new TwigFunction('getViewType',['Arikaim\\Core\\Paginator\\SessionPaginator','getViewType']),
            // database            
            new TwigFunction('applySearch',['Arikaim\\Core\\Db\\Search','apply']),
            new TwigFunction('createSearch',['Arikaim\\Core\\Db\\Search','setSearchCondition']),
            new TwigFunction('searchValue',['Arikaim\\Core\\Db\\Search','getSearchValue']),
            new TwigFunction('getSearch',['Arikaim\\Core\\Db\\Search','getSearch']),
            new TwigFunction('getOrderBy',['Arikaim\\Core\\Db\\OrderBy','getOrderBy']),
            new TwigFunction('applyOrderBy',['Arikaim\\Core\\Db\\OrderBy','apply']),
            new TwigFunction('createModel',[$this,'createModel']),
            new TwigFunction('showSql',['Arikaim\\Core\\Db\\Model','getSql']),
            // other
            new TwigFunction('getConstant',['Arikaim\\Core\\Db\\Model','getConstant']),
            new TwigFunction('hasExtension',[$this,'hasExtension']),
            new TwigFunction('getFileType',[$this,'getFileType']),         
            new TwigFunction('system',[$this,'system']),  
            new TwigFunction('getSystemRequirements',[$this,'getSystemRequirements']),                      
            new TwigFunction('package',[$this,'createPackageManager']),       
            new TwigFunction('service',[$this,'getService']),     
            new TwigFunction('installConfig',[$this,'getInstallConfig']),     
            new TwigFunction('access',[$this,'getAccess']),   
            new TwigFunction('getCurrentLanguage',[$this,'getCurrentLanguage']),
            new TwigFunction('getVersion',[$this,'getVersion']),
            new TwigFunction('getLastVersion',[$this,'getLastVersion']),
            new TwigFunction('composerPackages',[$this,'getComposerPackages']),
            new TwigFunction('modules',[$this,'getModulesService']),

            // session vars
            new TwigFunction('getSessionVar',[$this,'getSessionVar']),
            new TwigFunction('setSessionVar',[$this,'setSessionVar']),

            new TwigFunction('getOption',[$this,'getOption']),
            new TwigFunction('getOptions',[$this,'getOptions']),
            new TwigFunction('csrfToken',[$this,'csrfToken']),                
            new TwigFunction('fetch',[$this,'fetch']),
            new TwigFunction('extractArray',[$this,'extractArray'],['needs_context' => true]),
            new TwigFunction('arikaimStore',[$this,'arikaimStore']),

            // url
            new TwigFunction('getPageUrl',[$this,'getPageUrl']),         
            new TwigFunction('getTemplateUrl',['Arikaim\\Core\\Http\\Url','getTemplateUrl']),     
            new TwigFunction('getLibraryUrl',['Arikaim\\Core\\Http\\Url','getLibraryFileUrl']),  
            new TwigFunction('getExtensionViewUrl',['Arikaim\\Core\\Http\\Url','getExtensionViewUrl']),     

            // files
            new TwigFunction('getDirectoryFiles',[$this,'getDirectoryFiles']),
            new TwigFunction('isImage',['Arikaim\\Core\\Utils\\File','isImageMimeType']),

            // date and time
            new TwigFunction('getTimeZonesList',['Arikaim\\Core\\Utils\\DateTime','getTimeZonesList']),
            new TwigFunction('timeInterval',['Arikaim\\Core\\Utils\\TimeInterval','getInterval']),
            new TwigFunction('currentYear',[$this,'currentYear']),
            new TwigFunction('today',['Arikaim\\Core\\Utils\\DateTime','getTimestamp']),
            // unique Id
            new TwigFunction('createUuid',['Arikaim\\Core\\Utils\\Uuid','create']),
            new TwigFunction('createToken',['Arikaim\\Core\\Utils\\Utils','createToken']),
        ];
        $this->cache->save('arikaim.twig.functions',$items,10);

        return $items;
    }

    /**
     * Get modules service
     *
     * @return object|null
     */
    public function getModulesService()
    {
        return (Arikaim::getContainer()->has('modules') == true) ? Arikaim::modules() : null;
    }

    /**
     * Get session var
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getSessionVar($name, $default = null)
    {
        return Session::get('vars.' . $name,$default);
    }

    /**
     * Set session var
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setSessionVar($name, $value)
    {
        Session::set('vars.' . $name,$value);
    }

    /**
     * Get page url
     *
     * @param string $routeName
     * @param string $extension
     * @param array $params
     * @param boolean $full
     * @param string|null $language
     * @return string|false
     */
    public function getPageUrl($routeName, $extension, $params = [], $full = true, $language = null)
    {
        $route = Arikaim::routes()->getRoutes([
            'name'           => $routeName,
            'extension_name' => $extension
        ]);

        if (isset($route[0]) == false) {
            return false;
        }
        $urlPath = Route::getRouteUrl($route[0]['pattern'],$params);
        
        return Page::getUrl($urlPath,$full,$language);
    }

    /**
     * Get paginator url
     *
     * @param string $pageUrl
     * @param integer $page
     * @param boolean $full
     * @param boolean $withLanguagePath
     * @return string
     */
    public function getPaginatorUrl($pageUrl, $page, $full = true, $withLanguagePath = false)
    {
        $path = (empty($pageUrl) == true) ? $page : $pageUrl . '/' . $page;
        
        return Page::getUrl($path,$full,$withLanguagePath);
    }

    /**
     * Template engine filters
     *
     * @return array
     */
    public function getFilters() 
    {      
        $items = $this->cache->fetch('arikaim.twig.filters');
        if (\is_array($items) == true) {
            return $items;
        }

        $items =  [
            new TwigFilter('jsonDecode',['Arikaim\\Core\\Utils\\Utils','jsonDecode']),
            // date time
            new TwigFilter('dateFormat',['Arikaim\\Core\\Utils\\DateTime','dateFormat']),
            new TwigFilter('dateTimeFormat',['Arikaim\\Core\\Utils\\DateTime','dateTimeFormat']),
            new TwigFilter('timeFormat',['Arikaim\\Core\\Utils\\DateTime','timeFormat']),
            // numbers
            new TwigFilter('numberFormat',['Arikaim\\Core\\Utils\\Number','format']),
            // text
            new TwigFilter('mask',['Arikaim\\Core\\Utils\\Text','mask']),
            new TwigFilter('pad',['Arikaim\\Core\\Utils\\Text','pad']),
            new TwigFilter('padLeft',['Arikaim\\Core\\Utils\\Text','padLeft']),
            new TwigFilter('padRight',['Arikaim\\Core\\Utils\\Text','padRight']),
            // files
            new TwigFilter('fileSize',['Arikaim\\Core\\Utils\\File','getSizeText']),
            new TwigFilter('baseName',['Arikaim\\Core\\Utils\\File','baseName']),
            new TwigFilter('relativePath',['Arikaim\\Core\\Utils\\Path','getRelativePath'])
        ];

        $this->cache->save('arikaim.twig.filters',$items,10);
        
        return $items;
    }

    /**
     * Template engine tests
     *
     * @return array
     */
    public function getTests() 
    {
       return [];
    }

    /**
     * Template engine tags
     *
     * @return array
     */
    public function getTokenParsers()
    {
        return [];
    }   

    /**
     * Get accesss
     *
     * @return AuthInterface
     */
    public function getAccess()
    {
        return $this->access;
    } 

    /**
     * Get composer packages info
     *
     * @param array $packagesList
     * @return array|false
     */
    public function getComposerPackages(array $packagesList)
    {
        // Control Panel only
        if ($this->access->hasControlPanelAccess() == false) {           
            return false;
        }

        return Composer::getLocalPackagesInfo(ROOT_PATH . BASE_PATH,$packagesList);
    }

    /**
     * Create arikaim store instance
     *
     * @return ArikaimStore
     */
    public function arikaimStore()
    {
        return new ArikaimStore();
    }

    /**
     * Get install config data
     *
     * @return array|false
     */
    public function getInstallConfig()
    {
        $daibled = Arikaim::config()->getByPath('settings/disableInstallPage');
       
        return ($daibled == true) ? false : Arikaim::get('config');         
    }

    /**
     * Get system requirements
     *
     * @return array
     */
    public function getSystemRequirements()
    {
        return Install::checkSystemRequirements();
    }

    /**
     * Get composer package current version
     *
     * @param string|null $packageName
     * @return string|false
     */
    public function getVersion($packageName = null)
    {
        $packageName = (empty($packageName) == true) ? Arikaim::getCorePackageName() : $packageName;       

        return Composer::getInstalledPackageVersion(ROOT_PATH . BASE_PATH,$packageName);     
    }

    /**
     * Get composer package last version
     *
     * @param  string|null $packageName
     * @return string|false
     */
    public function getLastVersion($packageName = null)
    {
        $packageName = (empty($packageName) == true) ? Arikaim::getCorePackageName() : $packageName;
        $update = new Update($packageName);
        
        return $update->getLastVersion();
    }

    /**
     * Get service from container
     *
     * @param string $name
     * @return mixed
     */
    public function getService($name)
    {
        if (\in_array($name,$this->protectedServices) == true) {
            return ($this->access->hasControlPanelAccess() == true) ? Arikaim::get($name) : false;           
        }

        if (\in_array($name,$this->userProtectedServices) == true) {
            return ($this->access->isLogged() == true) ? Arikaim::get($name) : false;           
        }

        return Arikaim::get($name);
    }

    /**
     * Get directory contents
     *
     * @param string $path
     * @param boolean $recursive
     * @param string $fileSystemName
     * @return array|false
     */
    public function getDirectoryFiles($path, $recursive = false, $fileSystemName = 'storage')
    {
        // Control Panel only
        if ($this->access->isLogged() == false) {
            return false;
        }

        return Arikaim::storage()->listContents($path,$recursive,$fileSystemName);
    }

    /**
     * Create package manager
     *
     * @param string $packageType
     * @return PackageManagerInterface|false
     */
    public function createPackageManager($packageType)
    {
        // Control Panel only
        if ($this->access->hasControlPanelAccess() == false) {
            return false;
        }
        
        return \Arikaim\Core\Arikaim::get('packages')->create($packageType);
    }

    /**
     * Create model 
     *
     * @param string $modelClass
     * @param string|null $extension
     * @param boolean $showError
     * @param boolean $checkTable
     * @return Model|false
     */
    public function createModel($modelClass, $extension = null, $showError = false, $checkTable = false)
    {
        if (\in_array($modelClass,$this->protectedModels) == true) {
            return ($this->access->hasControlPanelAccess() == true) ? Model::create($modelClass,$extension) : false;           
        }
        $model = Model::create($modelClass,$extension,null,$showError);

        if (\is_object($model) == true && $checkTable == true) {
            // check if table exist
            return (Schema::hasTable($model) == false) ? false : $model;
        }

        return $model;
    }

    /**
     * Return true if extension exists
     *
     * @param string $extension
     * @return boolean
     */
    public function hasExtension($extension)
    {
        $model = Model::Extensions()->where('name','=',$extension)->first();  

        return \is_object($model);          
    }

    /**
     * Return file type
     *
     * @param string $fileName
     * @return string
     */
    public function getFileType($fileName) 
    {
        return \pathinfo($fileName,PATHINFO_EXTENSION);
    }

    /**
     * Return current year
     *
     * @return string
     */
    public function currentYear()
    {
        return \date('Y');
    }
    
    /**
     * Return current language
     *
     * @return array|null
     */
    public function getCurrentLanguage() 
    {
        $language = Arikaim::get('page')->getLanguage();
        $model = Model::Language()->where('code','=',$language)->first();

        return (\is_object($model) == true) ? $model->toArray() : null;
    }

    /**
     * Get option
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getOption($name, $default = null) 
    {
        return $this->options->get($name,$default);          
    }

    /**
     * Get options
     *
     * @param string $searchKey
     * @return array
     */
    public function getOptions($searchKey)
    {
        return $this->options->searchOptions($searchKey);       
    }

    /**
     * Create obj
     *
     * @param string $class
     * @param string|null $extension
     * @return object|null
     */
    public function create($class, $extension = null)
    {
        if (\class_exists($class) == false) {
            $class = (empty($extension) == false) ? Factory::getExtensionClassName($extension,$class) : Factory::getFullClassName($class);
        }
     
        return Factory::createInstance($class);            
    }
    
    /**
     * Return csrf token field html code
     *
     * @return string
     */
    public function csrfToken()
    {
        $token = Csrf::getToken(true);    

        return "<input type='hidden' name='csrf_token' value='" . $token . "'>";
    }

    /**
     * Fetch url
     *
     * @param string $url
     * @return Response|null
     */
    public function fetch($url)
    {
        $response = \Arikaim\Core\Arikaim::get('http')->get($url);
        
        return (\is_object($response) == true) ? $response->getBody() : null;
    }

    /**
     * Exctract array as local variables in template
     *
     * @param array $context
     * @param array $data
     * @return void
     */
    public function extractArray(&$context, $data) 
    {
        if (\is_array($data) == false) {
            return;
        }
        foreach($data as $key => $value) {
            $context[$key] = $value;
        }
    }  

    /**
     * Get system info ( control panel access only )
     *
     * @return System
     */
    public function system()
    { 
        return ($this->access->hasControlPanelAccess() == true) ? new System() : null;
    }
}
