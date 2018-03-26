<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\View\Html;

use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Html\BaseComponent;
use Arikaim\Core\View\Template;
use Arikaim\Core\Utils\Collection;
use Arikaim\Core\Interfaces\View\ComponentViewInterface;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Interfaces\View\ComponentInterface;

class Page extends BaseComponent implements ComponentViewInterface
{   
    protected $head;
    protected $properties;

    public function __construct() {
        parent::__construct();
        $this->setOptionsFileName("page.json");  
        $this->head = [];
        $this->properties = new Collection();  
    }

    public function properties()
    {
        return $this->properties;
    }

    public function load($name, $params = [], $language = null)
    {     
        $component = $this->render($name,$params,$language);
        return Arikaim::response()->getBody()->write($component->getHtmlCode());
    }

    public function render($name, $params = [], $language = null)
    {
        $component = $this->create($name,'pages',$language);

        if ($component->hasContent() == false) {
            if ($component->getType() != Template::SYSTEM) {
                $component = $this->create("system:" . $component->getPath(),'pages',$language);                
            }
        }
        
        if ($component->hasContent() == false) {
            $component = $this->render('page-not-found',$params);
        }
        $loader = $component->getOption('loader');
        if (empty($loader) == false) {
            Arikaim::session()->set("template.loader",$loader);
        }

        $page_body = $this->getCode($component,$params);
        $index_page = $this->getIndexPath($component->getType());

        $params = array_merge($params,['body' => $page_body, 'head' => $this->head]);
        $component->setHtmlCode(Arikaim::view()->fetch($index_page,$params));
        return $component;
    }

    private function getIndexPath($page_type)
    {
        if ($page_type == Template::SYSTEM) {
            return Template::SYSTEM_TEMPLATE_NAME . DIRECTORY_SEPARATOR . "index.html";
        }
        return Template::getTemplateName() . DIRECTORY_SEPARATOR . "index.html";
    }

    public function getCode(ComponentInterface $component, $params = [])
    {
        $this->includeFiles($component);
        $this->processPageOptions($component);

        $this->setCurrent($component->getpath());
        Template::includeFiles($component->getType());

        $properties = $component->getProperties();
        if (isset($properties['head']) == true) {
            $this->head = array_merge($this->head,$properties['head']);
        }
        $params = array_merge_recursive($params,$properties);
        return Arikaim::view()->fetch($component->getTemplateFile(),$params);
    }
    
    public function processPageOptions(ComponentInterface $component)
    {
        // option:  include/library
        // TODO
    }

    public function has($page_name) 
    {
        $page = $this->create($page_name,'pages');
        return ($page->isValid() == false) ? false : true;          
    }

    public function includeFiles(ComponentInterface $component)
    {
        // js file
        $js_files = $component->getFiles('js');
        foreach ($js_files as $file) {
            Arikaim::page()->properties()->add('include.components.js',$file['url']);
        }
        // css files
        $css_files = $component->getFiles('css');
        foreach ($css_files as $file) {
            Arikaim::page()->properties()->add('include.page.css',$file['url']);
        }   
    }

    public function setHeadAttribute($name,$value)
    {
        $this->head[$name] = $value;
    }

    public function setHead(array $head)
    {
        $this->head = $head;
    }

    public static function getPageJsFiles()
    {
        return Arikaim::page()->properties()->get('include.page.js');
    }

    public static function getPageCssFiles()
    {
        return Arikaim::page()->properties()->get('include.page.css');
    }

    public static function getComponentsJsFiles()
    {
        return Arikaim::page()->properties()->get('include.components.js');
    }

    public static function getComponentsCssFiles()
    {
        return Arikaim::page()->properties()->get('include.components.css');
    }

    public function setCurrent($name)
    {
        Arikaim::session()->set("current.page",$name);
    }

    public static function getCurrent()
    {
        return Arikaim::session()->get("current.page");
    }

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

    public static function getCurrentUrl($full = true)
    {
        $url = "";
        if ($full == true) {
            $url = Arikaim::getBaseURL();
        }
        $path =  Arikaim::session()->get('current.path');
        return $url . $path;
    }

    public static function getUrl($path = null, $full = false)
    {       
        if ($full == true) {
            $url = Arikaim::getBaseURL();
        } else {
            $url = Arikaim::getBasePath();
        }
        if ($url == "/") {
            $url = "";
        }
        return $url . "/" . Self::getLanguagePath($path);
    }

    public static function getFullUrl($path)
    {
        return Self::getUrl($path,true);
    }
}
