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
use Arikaim\Core\Controler;
use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\View\Html\Template;
use Arikaim\Core\View\Html\HtmlComponent;
use Arikaim\Core\Extension\ExtensionsManager;

class Page extends HtmlComponent
{
    protected $current_page;
    
    public function __construct() {
        parent::__construct();
        $this->setRootPath("pages");
    }

    public function loadPage($full_page_name, $params = [])
    {
        $page_code = $this->getPageCode($full_page_name, $params);
        $response = Arikaim::response();
        $view = Arikaim::view();
        $twig = $view->getEnvironment(); 
        $twig->addGlobal('page',$page_code);
        
        $response = $view->render($response,"index.html",$params);  
        return $response;
    }

    public function getPageCode($full_page_name, $params = [])
    {
        $page_name = $this->parseName($full_page_name);
        $page_path = $page_name['path'];
        $type = $page_name['type'];
    
        Page::setCurrentPage($full_page_name);
        Arikaim::set('pageType',$type);
        Arikaim::template()->includeTemplateFiles($type);
        $this->includePageFiles($page_path,$type);
        $page_code = $this->fetch($page_path,$type,$params);
        return $page_code;
    }

    public function hasPage($full_page_name)
    {
        $page_name = $this->parseName($full_page_name);
        $page_path = $page_name['path'];
        $type = $page_name['type'];
        $template_name = $this->getPageFile($path); 
        return File::exists($template_name);
    }   

    public function fetch($page_path,$type, $params = []) 
    {
        if (is_array($params) == false) $params = []; 
        $vars = $this->loadParams($page_path,$type);
        $params = array_merge_recursive($params,$vars);
        
        $path = $this->getPath($page_path,$type,false);       
        $template_name = $this->getPageFile($path);      
        return Arikaim::view()->fetch($template_name,$params);
    }

    public function getPageFile($path) 
    {        
        $parts = explode('/',$path);
        $page_name = end($parts);

        $template_name = $path . DIRECTORY_SEPARATOR . "$page_name.html";
        return $template_name;
    }

    public function pageExists($full_page_name) 
    {
        $page_name = $this->parseName($full_page_name);
        $page_path = $page_name['path'];
        $type = $page_name['type'];
        $path = $this->getPath($page_path,$type,true);      
        $template_name = $this->getPageFile($path);    
        return File::exists($template_name);
    }

    public function loadParams($full_page_name, $type)
    {        
        $parts = explode('/',$full_page_name);
        $page_name = end($parts);
        $page_path = $this->getPath($full_page_name,$type);
        $page_file = $this->getPropertiesFileName($page_path,$page_name,true);
        $data = File::loadJSONFile($page_file);
        if (is_array($data['page']) == true ) {
            return $data['page'];
        }
        return [];
    }

    public function includePageFiles($full_page_name, $type)
    {
        $parts = explode('/',$full_page_name);
        $page_name = end($parts);
        $path = $this->getPath($full_page_name,$type);

        // js file
        $full_file_name = $path . DIRECTORY_SEPARATOR . "$page_name.js";
        if (File::exists($full_file_name) == true) {
            $file_url = $this->getUrl($full_page_name,$type) . "/" . "$page_name.js";
            Arikaim::page('properties')->add('include.page.js',$file_url);
        }
        // css file
        $full_file_name = $path . DIRECTORY_SEPARATOR . "$page_name.css";
        if (File::exists($full_file_name) == true) {
            $file_url = $this->getUrl($full_page_name,$type) . "/" . "$page_name.css";
            Arikaim::page('properties')->add('include.page.css',$file_url);
        }   
    }

    public function getPageJSFiles()
    {
        return Arikaim::page('properties')->get('include.page.js');
    }

    public function getPageCSSFiles()
    {
        return Arikaim::page('properties')->get('include.page.css');
    }

    public function getPageType()
    {     
        return Arikaim::pageType();
    }
    
    public static function setCurrentPage($name)
    {
        Arikaim::session()->set("current_page",$name);
    }

    public static function getCurrentPage()
    {
        return Arikaim::session()->get("current_page");
    }
}
