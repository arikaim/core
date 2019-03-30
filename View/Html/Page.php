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
use Arikaim\Core\View\Html\Component;
use Arikaim\Core\View\Template;
use Arikaim\Core\Utils\Collection;
use Arikaim\Core\Interfaces\View\ComponentViewInterface;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Interfaces\View\ComponentInterface;
use Arikaim\Core\System\Url;

/**
 * Load html page templates
 */
class Page extends BaseComponent  
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

    public function loadPage($name, $params = [], $language = null)
    {
        $html = $this->load($name,$params,$language);
        Arikaim::response()->getBody()->write($html);
        return Arikaim::response();
    }

    public function load($name, $params = [], $language = null)
    {     
        $component = $this->render($name,$params,$language);
        return $component->getHtmlCode();
    }

    public function render($name, $params = [], $language = null)
    {
        $component = $this->create($name,'pages',$language);
        $params['component_url'] = $component->getUrl();

        if ($component->hasContent() == false) {          
            $component = $this->render('system:page-not-found',$params);
        }
        $loader = $component->getOption('loader');
        if (empty($loader) == false) {
            Arikaim::session()->set("template.loader",$loader);
        }

        $page_body = $this->getCode($component,$params);
        $index_page = $this->getIndexFile($component->getTemplateName());

        $params = array_merge($params,['body' => $page_body, 'head' => $this->head]);

        $component->setHtmlCode(Arikaim::view()->fetch($index_page,$params));
        return $component;
    }

    private function getIndexFile($template_name)
    {
        return $template_name . DIRECTORY_SEPARATOR . "index.html";
    }

    public function getCode(ComponentInterface $component, $params = [])
    {
        $this->includeFiles($component);
        $this->setCurrent($component->getPath());
              
      //  echo "t:" . $component->getTemplateName();

        Template::includeFiles($component->getTemplateName());
    
        $properties = $component->getProperties();
        if (isset($properties['head']) == true) {
            $this->head = array_merge($this->head,$properties['head']);
        }
        $params = array_merge_recursive($params,(array)$properties);
        return Arikaim::view()->fetch($component->getTemplateFile(),$params);
    }
    
    public function has($page_name) 
    {
        $page = $this->create($page_name,'pages');
        return ($page->isValid() == false) ? false : true;          
    }

    public function includeFiles(ComponentInterface $component)
    {
        $js_files = $component->getFiles('js'); 
        Arikaim::page()->properties()->merge('include.components.js',array_column($js_files,'url'));
      
        $css_files = $component->getFiles('css');     
        Arikaim::page()->properties()->merge('include.page.css',array_column($css_files,'url'));     
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
        $url = ($full == true) ? Url::ARIKAIM_BASE_URL : "";     
        $path = Arikaim::session()->get('current.path');
        return $url . $path;
    }

    /**
     * Return url link with current language code
     *
     * @param string $path
     * @param boolean $full
     * @return string
     */
    public static function getUrl($path = null, $full = false)
    {       
        $url = ($full == true) ? Url::ARIKAIM_BASE_URL : ARIKAIM_BASE_PATH;        
        $url = ($url == "/") ? "" : $url;            
        return $url . "/" . Self::getLanguagePath($path);
    }

    public static function getFullUrl($path)
    {
        return Self::getUrl($path,true);
    }
}
