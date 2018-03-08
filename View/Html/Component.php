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
use Arikaim\Core\Interfaces\View\ComponentView;

class Component extends BaseComponent implements ComponentView
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

    public function load($name, $params = [])
    {       
        $component = $this->render($name,$params);
        if ($this->hasError($component) == true) {
            return $component['error'];
        }
        return $component['html_code'];
    }

    public function render($name, $vars = []) 
    {    
        $component = $this->resolve($name);
        
        if ($this->hasError($component) == true) {
            return $component;
        }      
       
        if ($component['type'] == Template::EXTENSION) {
            $path = ExtensionsManager::getExtensionViewPath($component['extension_name']);               
            Arikaim::view()->addPath($path); 
        }
       
        $this->includeFiles($component);
       
        $params = Arrays::merge($component['properties'],Arikaim::view()->components()->get($component['path']));      
        $params = Arrays::merge($params,$vars);

        Arikaim::view()->components()->set($component['path'],$params);          
        return $this->fetch($component,$params);
    }

    public function includeFiles($component) 
    {
        // js file
        if (isset($component['files']['js']) == true) {
            Arikaim::page()->properties()->add('include.components.js',$component['files']['js']['url']);
            Arikaim::view()->component()->files()->add("js_files",$component['files']['js']['url']);
        }
        // css file
        if (isset($component['files']['css']) == true) {
            Arikaim::page()->properties()->add('include.components.css',$component['files']['css']['url']);
            Arikaim::view()->component()->files()->add("css_files",$component['files']['css']['url']);
        }
    }
    
    public function getComponentProperties($component_name)
    {
        $component = $this->resolve($component_name);
        return $this->getProperties($component);
    }

    public function getComponentDetails($component_name)
    {
        $component = $this->resolve($component_name);
        $options = $this->loadOptions($details['path'],$details['type']);
        $component['auth'] = $options->getByPath("access/auth",$this->default_auth);
        $component['permissions'] = $options->getByPath("access/permissions",[]);
        return $component;
    }
}
