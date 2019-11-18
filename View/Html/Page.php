<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\View\Html;

use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Html\Component;
use Arikaim\Core\View\Html\BaseComponent;
use Arikaim\Core\View\Html\HtmlComponent;
use Arikaim\Core\View\Template\Template;
use Arikaim\Core\Collection\Collection;
use Arikaim\Core\View\Html\PageHead;
use Arikaim\Core\System\Url;
use Arikaim\Core\System\Path;
use Arikaim\Core\View\Theme;
use Arikaim\Core\Packages\Template\TemplatesManager;
use Arikaim\Core\Packages\Library\LibraryManager;
use Arikaim\Core\Utils\File;
use Arikaim\Core\Collection\Arrays;
use Arikaim\Core\Utils\Text;

use Arikaim\Core\Interfaces\View\ComponentInterface;

/**
 * Html page
 */
class Page extends BaseComponent  
{   
    /**
     *  Error page names
     */
    const PAGE_NOT_FOUND         = 'page-not-found';
    const SYSTEM_ERROR_PAGE      = 'system-error';
    const APPLICATION_ERROR_PAGE = 'application-error';

    /**
     * Page head properties
     *
     * @var PageHead
     */
    protected $head;
    
    /**
     *Page properties
     *
     * @var Collection
     */
    protected $properties;

    /**
     * Constructor
     */
    public function __construct() {  
        $this->head = new PageHead();
        $this->properties = new Collection();  
    }

    /**
     * Create pgae
     *
     * @param string $name
     * @param string|null $language
     * @param boolean $withOptions
     * @return ComponentInterface
     */
    public function create($name, $language = null, $withOptions = true)
    {       
        return Self::createComponent($name,'pages',$language,$withOptions,'page.json');
    }

    /**
     * Get properties
     *
     * @return Collection
     */
    public function properties()
    {
        return $this->properties;
    }

    /**
     * Get head properties
     *
     * @return PageHead
     */
    public function head()
    {
        return $this->head;
    }

    /**
     * Load page
     *
     * @param string $name
     * @param array|object $params
     * @param string|null $language
     * @return object
     */
    public function load($name, $params = [], $language = null, $response = null)
    {
        $response = ($response == null) ? Arikaim::response() : $response;
        if (empty($name) == true || $this->has($name) == false) {         
            $name = $this->resoveErrorPageName(Self::PAGE_NOT_FOUND);
            $response->withStatus(404);          
        }
        if (is_object($params) == true) {
            $params = $params->toArray();
        }
      
        $component = $this->render($name,$params,$language);  
        $html = $component->getHtmlCode();
        $response->getBody()->write($html);

        return $response;
    }

    /**
     * Render page
     *
     * @param string $name
     * @param array $params
     * @param string|null $language
     * @return ComponentInterface
     */
    public function render($name, $params = [], $language = null)
    {
        $this->setCurrent($name);
        $component = $this->create($name,$language);
        $params['component_url'] = $component->getUrl();

        if ($component->hasContent() == false) {             
            $component = $this->renderPageNotFound($params,$language);
        }
        
        $body = $this->getCode($component,$params);
        $indexPage = $this->getIndexFile($component);              
        $params = array_merge($params,['body' => $body, 'head' => $this->head->toArray()]);   
        $component->setHtmlCode(Arikaim::view()->fetch($indexPage,$params));

        return $component;
    }

    /**
     * Get page index file
     *
     * @param object $component
     * @return string
     */
    private function getIndexFile($component)
    {
        $type = $component->getType();
        $fullPath = Path::getTemplatePath($component->getTemplateName(),$type) . $component->getBasePath() . DIRECTORY_SEPARATOR . "index.html";

        if (file_exists($fullPath) == true) {
            if ($type == Component::EXTENSION_COMPONENT) {
                return $component->getTemplateName() . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $component->getBasePath() . DIRECTORY_SEPARATOR . "index.html"; 
            } 
            return $component->getTemplateName() . DIRECTORY_SEPARATOR . $component->getBasePath() . DIRECTORY_SEPARATOR . "index.html";            
        }
        // get from current template  
        $fullPath = Path::getTemplatePath(Template::getTemplateName()) . $component->getBasePath() . DIRECTORY_SEPARATOR . "index.html";      
        if (file_exists($fullPath) == true) {          
            return Template::getTemplateName() . DIRECTORY_SEPARATOR . $component->getBasePath() . DIRECTORY_SEPARATOR . "index.html";
        }

        // get from system template
        return Template::SYSTEM_TEMPLATE_NAME . DIRECTORY_SEPARATOR . $component->getBasePath() . DIRECTORY_SEPARATOR . "index.html";          
    }

    /**
     * Get page code
     *
     * @param ComponentInterface $component
     * @param array $params
     * @return string
     */
    public function getCode(ComponentInterface $component, $params = [])
    {     
        // include component files
        Arikaim::page()->properties()->merge('include.page.files',$component->getFiles());
            
        Self::includeFiles($component);
        $properties = $component->getProperties();
        
        if (isset($properties['head']) == true) {
            $head = Text::renderMultiple($properties['head'],$this->head->getParams()); 
            $this->head->replace($head); 
            if (isset($head['og']) == true) {
                $this->head->set('og',$head['og']);
                $this->head->resolveProperties('og');
            }
            if (isset($head['twitter']) == true) {
                $this->head->set('twitter',$head['twitter']);
                $this->head->resolveProperties('twitter');
            }         
        }
        $params = array_merge_recursive($params,(array)$properties);

        return Arikaim::view()->fetch($component->getTemplateFile(),$params);
    }
    
    /**
     * Return true if page exists
     *
     * @param string $pageName
     * @param string|null $language
     * @return boolean
     */
    public function has($pageName, $language = null) 
    {      
        $page = $this->create($pageName,$language);

        return $page->isValid();        
    }

    /**
     * Set page head properties
     *
     * @param Collection $head
     * @return void
     */
    public function setHead(Collection $head)
    {
        $this->head = $head;
    }

    /**
     * Get page fles
     *
     * @return array
     */
    public static function getPageFiles()
    {
        return Arikaim::page()->properties()->get('include.page.files');        
    }

    /**
     * Get component files
     *
     * @return array
     */
    public static function getComponentsFiles()
    {    
        return Arikaim::page()->properties()->get('include.components.files');
    }

    /**
     * Set curret page
     *
     * @param string $name
     * @return void
     */
    public function setCurrent($name)
    {   
        Arikaim::session()->set("page.name",$name);
    }

    /**
     * Get current page name
     *
     * @return string
     */
    public static function getCurrent()
    {
        return Arikaim::session()->get("page.name");
    }

    /**
     * Get language path
     *
     * @param string $path
     * @param string|null $language
     * @return string
     */
    public static function getLanguagePath($path, $language = null)
    {
        if ($language == null) {
            $language = Template::getLanguage();
        }
       
        return (substr($path,-1) == "/") ?  $path . "$language/" : "$path/$language/";
    }

    /**
     * Get curret page url
     *
     * @param boolean $full
     * @return string
     */
    public static function getCurrentUrl($full = true)
    {       
        $path = Arikaim::session()->get('current.path');

        return ($full == true) ? Self::getFullUrl($path) : $path;
    }

    /**
     * Return url link with current language code
     *
     * @param string $path
     * @param boolean $full
     * @param boolean $withLanguagePath
     * @return string
     */
    public static function getUrl($path = null, $full = false, $withLanguagePath = false)
    {       
        $path = (substr($path,0,1) == "/") ? substr($path, 1) : $path;           
        $url = ($full == true) ? Url::ARIKAIM_BASE_URL : ARIKAIM_BASE_PATH;        
        $url = ($url == "/") ? $url : $url . "/";       

        return ($withLanguagePath == true) ? $url . Self::getLanguagePath($path) : $url;
    }

    /**
     * Get full page url
     *
     * @param string $path
     * @return string
     */
    public static function getFullUrl($path)
    {
        return Self::getUrl($path,true);
    }

    /**
     * Include files
     *
     * @param Component $component
     * @return bool
     */
    public static function includeFiles($component) 
    {
        $files = Self::getPageIncludeOptions($component);
        $files = Arrays::setDefault($files,'library',[]);            
        $files = Arrays::setDefault($files,'loader',false);       
              
        Self::includeComponents($component);

        Arikaim::cache()->save("page.include.files." . $component->getName(),$files,3);
        Arikaim::page()->properties()->set('template.files',$files);
        // include ui lib files                
        Self::includeLibraryFiles($files['library']);  
        // include theme files         
        $templateName = (empty($files['template']) == true) ? Template::getTemplateName() : $files['template'];
        Self::includeThemeFiles($templateName);  
      
        return true;
    }

    /**
     * Get page include options
     *
     * @param Component $component
     * @return array
     */
    public static function getPageIncludeOptions($component)
    {
        // from cache 
        $options = Arikaim::cache()->fetchPageIncludeFiles($component->getName());
        if (is_array($options) == true) {
            return $options;
        }

        // from page options
        $options = $component->getOption('include',null);
      
        if (empty($options) == false) {  
            // get include options from page.json file  
            $options = Arrays::setDefault($options,'template',null);   
            $options = Arrays::setDefault($options,'js',[]);  
            $options = Arrays::setDefault($options,'css',[]);   

            $url = Url::getExtensionViewUrl($component->getTemplateName());

            $options['js'] = array_map(function($value) use($url) {
                return $url . "/js/" . $value; 
            },$options['js']);
    
            $options['css'] = array_map(function($value) use($url) {
                return $url . "/css/" . $value; 
            },$options['css']);

            if (empty($options['template']) == false) {
                $options = array_merge($options,Self::getTemplateIncludeOptions($options['template']));              
            } elseif ($component->getType() == Component::TEMPLATE_COMPONENT) {
                $options = array_merge($options,Self::getTemplateIncludeOptions($component->getTemplateName())); 
            }                  
            // set loader from page.json
            if (isset($options['loader']) == true) {
                Arikaim::session()->set('template.loader',$options['loader']);
            }
           
            return $options;
        }

        // from current template 
        return Self::getTemplateIncludeOptions();
    }

    /**
     * Include components fiels set in page.json include/components
     *
     * @param Component $component
     * @return void
     */
    public static function includeComponents($component)
    {
        // include component files
        $components = $component->getOption('include/components',null);         
        if (empty($components) == true) {
            return;
        }  
        foreach ($components as $item) {                   
            $files = Self::getComponentFiles($item);      
            HtmlComponent::includeComponentFiles($files['js'],'js');
            HtmlComponent::includeComponentFiles($files['css'],'css');              
        }
    }

    /**
     * Get template include options
     *
     * @param string $name
     * @return array
     */
    public static function getTemplateIncludeOptions($name = null)
    {
        $name = ($name == null) ? Template::getTemplateName() : $name;
        $manager = new TemplatesManager();
        $templateOptions = $manager->createPackage($name)->getProperties();
        $options = $templateOptions->getByPath("include",[]);
    
        $options = Arrays::setDefault($options,'js',[]);  
        $options = Arrays::setDefault($options,'css',[]);   

        $url = Url::getTemplateUrl($name);    
      
        $options['js'] = array_map(function($value) use($url) {
            return $url . "/js/" . $value; 
        },$options['js']);

        $options['css'] = array_map(function($value) use($url) {
            return $url . "/css/" . $value; 
        },$options['css']);
      
        return $options;
    }

    /**
     * Include library files
     *
     * @param array $libraryList
     * @return bool
     */
    public static function includeLibraryFiles(array $libraryList)
    {   
        $manager = new LibraryManager();
        $frameworks = [];
        $includeLib = [];

        foreach ($libraryList as $libraryName) {
            $library = $manager->createPackage($libraryName);
            $files = $library->getFiles();
            $params = $library->getParams();

            foreach($files as $file) {
                $item['file'] = (Url::isValid($file) == true) ? $file : Url::getLibraryFileUrl($libraryName,$file);
                $item['type'] = File::getExtension(Path::getLibraryFilePath($libraryName,$file));
                $item['params'] = $params;
                $item['library'] = $libraryName;
                $item['async'] = $library->getProperties()->get('async',false);
                $item['crossorigin'] = $library->getProperties()->get('crossorigin',null);
                array_push($includeLib,$item);
            }           
            if ($library->isFramework() == true) {
                array_push($frameworks,$libraryName);
            }
        }

        Arikaim::page()->properties()->set('ui.library.files',$includeLib);       
        Arikaim::session()->set("ui.included.libraries",json_encode($libraryList));
        Arikaim::session()->set("ui.included.frameworks",json_encode($frameworks));

        return true;
    }

     /**
     * Include theme files
     *
     * @param string $templateName
     * @return bool
     */
    public static function includeThemeFiles($templateName)
    {  
        // cehck cache
        $fileUrl = Arikaim::cache()->fetch('template.theme.file');
        if (empty($fileUrl) == false) {
            Arikaim::page()->properties()->add('template.theme',$fileUrl);
            return true;
        }

        $manager = new TemplatesManager();
        $properties = $manager->createPackage($templateName)->getProperties();

        $manager = new LibraryManager();
        $defaultTheme = $properties->get("default-theme",null);
        $currentTheme = Theme::getCurrentTheme($templateName,$defaultTheme);

        if (empty($currentTheme) == true) {
            return true;
        } 
        
        $library = $properties->getByPath("themes/$currentTheme/library","");
        $libraryPackage = $manager->createPackage($library);
        // get theme from other template
        $template = $properties->getByPath("themes/$currentTheme/template","");
        $templateName = (empty($template) == false) ? $template : $templateName;
           
        if (empty($library) == false) {
            // load theme from library           
            $file = $libraryPackage->getThemeFile($currentTheme);
            $fileUrl = Url::getLibraryThemeFileUrl($library,$file,$currentTheme);
        } else {
            // load from template
            $file = $properties->getByPath("themes/$currentTheme/file","");
            $fileUrl = Url::getThemeFileUrl($templateName,$currentTheme,$file);
        }
        if (empty($fileUrl) == false) {
            $theme['name'] = $currentTheme;
            $theme['file'] = $file;
            Arikaim::page()->properties()->add('template.theme',$fileUrl);
            // saev to cache
            Arikaim::cache()->save('template.theme.file',$fileUrl,3);
            return true;
        }

        return false;
    }

    /**
     * Resolve error page name
     *
     * @param string $type
     * @param string|null $extension
     * @return string
     */
    public function resoveErrorPageName($type, $extension = null)
    {
        $pageName = (empty($extension) == true) ? 'system:' . $type : $extension . ">" . $type;  
        
        return ($this->has($pageName) == true) ? $pageName : 'system:' . $type;
    }

    /**
     * Load page not found error page.
     *
     * @param array $data
     * @param string|null $language
     * @param string|null $extension
     * @return Response
     */
    public function loadPageNotFound($data = [], $language = null, $extension = null)
    {        
        $name = $this->resoveErrorPageName(Self::PAGE_NOT_FOUND,$extension);
        $response = $this->load($name,$data,$language);   

        return $response->withStatus(404); 
    }

    /**
     * Load system error page.
     *
     * @param array $data
     * @param string|null $language
     * @param string|null $extension
     * @return Response
     */
    public function loadSystemError($data = [], $language = null, $extension = null)
    {        
        $name = $this->resoveErrorPageName(Self::SYSTEM_ERROR_PAGE,$extension);
        $data = array_merge([
            'errors' => Arikaim::errors()->getErrors()
        ],$data);
        $response = $this->load($name,$data,$language);   

        return $response->withStatus(404); 
    }

    /**
     * Render page not found 
     *
     * @param array $data
     * @param string|null $language
     * @param string|null $extension
     * @return Component
     */
    public function renderPageNotFound($data = [], $language = null, $extension = null)
    {
        $name = $this->resoveErrorPageName(Self::PAGE_NOT_FOUND,$extension);

        return $this->render($name,$data,$language);
    }

    /**
     * Render application error
     *
     * @param array $data
     * @param string|null $language
     * @param string|null $extension
     * @return Component
     */
    public function renderApplicationError($data = [], $language = null, $extension = null)
    {
        $name = $this->resoveErrorPageName(Self::APPLICATION_ERROR_PAGE,$extension);
      
        return $this->render($name,$data,$language);
    }
}
