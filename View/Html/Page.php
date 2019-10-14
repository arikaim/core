<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
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
use Arikaim\Core\Db\Model;
use Arikaim\Core\System\Url;
use Arikaim\Core\System\Path;
use Arikaim\Core\View\Theme;
use Arikaim\Core\Packages\Template\TemplatesManager;
use Arikaim\Core\Packages\Library\LibraryManager;
use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\Utils\Text;

use Arikaim\Core\Interfaces\View\ComponentInterface;

/**
 * Html page
 */
class Page extends BaseComponent  
{   
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
     * @param boolean $with_options
     * @return ComponentInterface
     */
    public function create($name, $language = null, $with_options = true)
    {       
        return Self::createComponent($name,'pages',$language,$with_options,'page.json');
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
    public function load($name, $params = [], $language = null)
    {
        $response = Arikaim::response();
        if (empty($name) == true || $this->has($name) == false) {          
            $name = 'system:page-not-found';
            $response->withStatus(404);          
        }
        if (is_object($params) == true) {
            $params = $params->toArray();
        }
      
        $component = $this->render($name,$params,$language);  
        $html = $component->getHtmlCode();
    
        return $response->write($html);
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
            $component = $this->render('system:page-not-found',$params);
        }
        
        $page_body = $this->getCode($component,$params);
        $index_page = $this->getIndexFile($component);
              
        $params = array_merge($params,['body' => $page_body, 'head' => $this->head->toArray()]);   
        $component->setHtmlCode(Arikaim::view()->fetch($index_page,$params));

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
        $full_path = Path::getTemplatePath($component->getTemplateName(),$type) . $component->getBasePath() . DIRECTORY_SEPARATOR . "index.html";

        if (file_exists($full_path) == true) {
            if ($type == Component::EXTENSION_COMPONENT) {
                return $component->getTemplateName() . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $component->getBasePath() . DIRECTORY_SEPARATOR . "index.html"; 
            } 
            return $component->getTemplateName() . DIRECTORY_SEPARATOR . $component->getBasePath() . DIRECTORY_SEPARATOR . "index.html";            
        }
        // get from current template  
        $full_path = Path::getTemplatePath(Template::getTemplateName()) . $component->getBasePath() . DIRECTORY_SEPARATOR . "index.html";      
        if (file_exists($full_path) == true) {          
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
            $this->head->resolveProperties('og');
            $this->head->resolveProperties('twitter');
         
            $this->head->replace($head);
        }
        $params = array_merge_recursive($params,(array)$properties);

        return Arikaim::view()->fetch($component->getTemplateFile(),$params);
    }
    
    /**
     * Return true if page exists
     *
     * @param string $page_name
     * @param string|null $language
     * @return boolean
     */
    public function has($page_name, $language = null) 
    {      
        $page = $this->create($page_name,$language);
        return (is_object($page) == false) ? false : $page->isValid();        
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
        $default_language = Model::Language()->getDefaultLanguage();
        if ($language == null) {
            $language = Template::getLanguage();
        }
        if ($default_language == $language) {
            return $path;
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
        $url = ($full == true) ? Url::ARIKAIM_BASE_URL : "";     
        $path = Arikaim::session()->get('current.path');
        return $url . $path;
    }

    /**
     * Return url link with current language code
     *
     * @param string $path
     * @param boolean $full
     * @param boolean $with_language_path
     * @return string
     */
    public static function getUrl($path = null, $full = false, $with_language_path = true)
    {       
        $path = (substr($path,0,1) == "/") ? substr($path, 1) : $path;           
        $url = ($full == true) ? Url::ARIKAIM_BASE_URL : ARIKAIM_BASE_PATH;        
        $url = ($url == "/") ? $url : $url . "/";            
        return ($with_language_path == true) ? $url . Self::getLanguagePath($path) : $url;
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
     * @param string $template_name
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
        $template_name = (empty($files['template']) == true) ? Template::getTemplateName() : $files['template'];
        Self::includeThemeFiles($template_name);  
        // set loader component       
 
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
        $template_options = $manager->createPackage($name)->getProperties();
        $options = $template_options->getByPath("include",[]);
    
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
     * @param array $library_list
     * @return bool
     */
    public static function includeLibraryFiles(array $library_list)
    {   
        $manager = new LibraryManager();
        $frameworks = [];
        $include_lib = [];

        foreach ($library_list as $library_name) {
            $library = $manager->createPackage($library_name);
            $files = $library->getFiles();
            $params = $library->getParams();

            foreach($files as $file) {
                $item['file'] = (Url::isValid($file) == true) ? $file : Url::getLibraryFileUrl($library_name,$file);
                $item['type'] = File::getExtension(Path::getLibraryFilePath($library_name,$file));
                $item['params'] = $params;
                $item['library'] = $library_name;
                $item['async'] = $library->getProperties()->get('async',false);
                array_push($include_lib,$item);
            }           
            if ($library->isFramework() == true) {
                array_push($frameworks,$library_name);
            }
        }
        Arikaim::page()->properties()->set('ui.library.files',$include_lib);       
        Arikaim::session()->set("ui.included.libraries",json_encode($library_list));
        Arikaim::session()->set("ui.included.frameworks",json_encode($frameworks));

        return true;
    }

     /**
     * Include theme files
     *
     * @param string $template_name
     * @return bool
     */
    public static function includeThemeFiles($template_name)
    {  
        // cehck cache
        $file_url = Arikaim::cache()->fetch('template.theme.file');
        if (empty($file_url) == false) {
            Arikaim::page()->properties()->add('template.theme',$file_url);
            return true;
        }

        $manager = new TemplatesManager();
        $properties = $manager->createPackage($template_name)->getProperties();

        $manager = new LibraryManager();
        $default_theme = $properties->get("default-theme",null);
        $current_theme = Theme::getCurrentTheme($template_name,$default_theme);

        if (empty($current_theme) == true) {
            return true;
        } 
        
        $library = $properties->getByPath("themes/$current_theme/library","");
        $library_package = $manager->createPackage($library);
        // get theme from other template
        $template = $properties->getByPath("themes/$current_theme/template","");
        $template_name = (empty($template) == false) ? $template : $template_name;
           
        if (empty($library) == false) {
            // load theme from library           
            $file = $library_package->getThemeFile($current_theme);
            $file_url = Url::getLibraryThemeFileUrl($library,$file,$current_theme);
        } else {
            // load from template
            $file = $properties->getByPath("themes/$current_theme/file","");
            $file_url = Url::getThemeFileUrl($template_name,$current_theme,$file);
        }
        if (empty($file_url) == false) {
            $theme['name'] = $current_theme;
            $theme['file'] = $file;
            Arikaim::page()->properties()->add('template.theme',$file_url);
            // saev to cache
            Arikaim::cache()->save('template.theme.file',$file_url,3);
            return true;
        }
        return false;
    }
}
