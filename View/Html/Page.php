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
use Arikaim\Core\Interfaces\View\ComponentView;
use Arikaim\Core\Db\Model;

class Page extends BaseComponent implements ComponentView
{
    protected $current_page;
    protected $head;
    protected $properties;

    public function __construct() {
        parent::__construct();
        $this->setRootPath("pages");
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
        return Arikaim::response()->getBody()->write($component['html_code']);
    }

    public function render($name, $params = [], $language = null)
    {
        $component = $this->resolve($name,$language);

        if ($this->hasContent($component) == false) {
            if ($component['type'] != Template::SYSTEM) {
                $component = $this->resolve("system:" . $component['path']);                
            }
        }
        
        if ($this->hasContent($component) == false) {
            $component =  $this->render('page-not-found',$params);
        }
        if (isset($component['options']['loader']) == true) {
            Arikaim::session()->set("template.loader",$component['options']['loader']);
        }

        $page_body = $this->getCode($component,$params);
        $index_page = $this->getIndexPath($component);

        $params = array_merge($params,['body' => $page_body, 'head' => $this->head]);
        $component['html_code'] = Arikaim::view()->fetch($index_page,$params);
        return $component;
    }

    private function getIndexPath($page)
    {
        if ($page['type'] == Template::SYSTEM) {
            return Template::SYSTEM_TEMPLATE_NAME . DIRECTORY_SEPARATOR . "index.html";
        }
        return Template::getTemplateName() . DIRECTORY_SEPARATOR . "index.html";
    }

    public function getCode($component, $params = [])
    {
        $this->includeFiles($component);
      
        $this->setCurrent($component['path']);
        Template::includeFiles($component['type']);

        $properties = $this->getProperties($component);
        if (isset($properties['head']) == true) {
            $this->head = array_merge($this->head,$properties['head']);
        }
        $params = array_merge_recursive($params,$properties);
        return Arikaim::view()->fetch($component['template_file'],$params);
    }
    
    public function has($full_page_name) 
    {
        $page = $this->resolve($full_page_name);
        if ($page == false) {
            return false;
        }
        return true;        
    }

    public function includeFiles($component)
    {
        // js file
        $file_url = isset($component['files']['js']['url']) ? $component['files']['js']['url'] : null;
        if (empty($file_url) == false) {
            Arikaim::page()->properties()->add('include.page.js',$file_url);
        }
        
        // css file
        $file_url = isset($component['files']['css']['url']) ? $component['files']['css']['url'] : null;
        if (empty($file_url) == false) {
            Arikaim::page()->properties()->add('include.page.css',$file_url);
        }
    }

    public function setHeadAttribute($name,$value)
    {
        $this->head[$name] = $value;
    }

    public function setHead(array $head_attributes)
    {
        $this->head = $head_attributes;
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
        Arikaim::session()->set("current_page",$name);
    }

    public static function getCurrent()
    {
        return Arikaim::session()->get("current_page");
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
        if (substr($path,-1) == "/") {
            $path = substr($path,0,-1);
        }
        return $url . "/" . Self::getLanguagePath($path);
    }

    public static function getFullUrl($path)
    {
        return Self::getUrl($path,true);
    }
}
