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
use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\Utils\Collection;
use Arikaim\Core\Extension\ExtensionsManager;
use Arikaim\Core\View\Html\BaseComponent;
use Arikaim\Core\View\Template;
use Arikaim\Core\Interfaces\View\ComponentViewInterface;

class HtmlComponent extends BaseComponent implements ComponentViewInterface
{
    private $files;

    public function __construct() {
        parent::__construct();
        $this->setRootPath("components");
        $this->files = new Collection();
    }

    public function files()
    {
        return $this->files;
    }

    public function getErrorMessage($component)
    {
        $params = ['message' => $component['error']];
        return $this->load('system:message.error',$params);
    }

    public function load($name, $params = [], $language = null)
    {       
        $component = $this->render($name,$params,$language);
        if ($component->hasError() == true) {
            return $this->getErrorMessage($component);
        }
        return $component->getHtmlCode();
    }

    public function render($name, $vars = [], $language = null) 
    {    
        $component = $this->create($name,$language);
        if ($component->hasError() == true) {
            return $component;
        }      
       
        if ($component->getType() == Template::EXTENSION) {
            $path = ExtensionsManager::getExtensionViewPath($component->getExtensionName());               
            Arikaim::view()->addPath($path); 
        }
       
        $this->includeFiles($component);
       
        $params = Arrays::merge($component->getProperties(),Arikaim::view()->components()->get($component->getPath()));      
        $params = Arrays::merge($params,$vars);

        Arikaim::view()->components()->set($component->getPath(),$params);          
        return $this->fetch($component,$params);
    }

    public function includeFiles($component) 
    {
        // js file
        $js_files = $component->getFiles('js');
        foreach ($js_files as $file) {
            Arikaim::page()->properties()->add('include.components.js',$file['url']);
            Arikaim::view()->component()->files()->add("js_files",$file['url']);
        }
        // css files
        $css_files = $component->getFiles('css');
        foreach ($css_files as $file) {
            Arikaim::page()->properties()->add('include.components.css',$file['url']);
            Arikaim::view()->component()->files()->add("css_files",$file['url']);
        }      
    }
    
    public function getComponentProperties($name, $params = [], $language = null)
    {
        $component = $this->render($name,$params,$language);
        if ($component->hasError() == true) {
            return $component->getError();
        }
        return $component->getProperties();
    }

    public function getComponentDetails($component_name)
    {
        $component = $this->create($component_name);
      
        return $component;
    }
}
