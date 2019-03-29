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
use Arikaim\Core\Interfaces\View\ComponentInterface;

class HtmlComponent extends BaseComponent implements ComponentViewInterface
{
    private $files;

    public function __construct() {
        parent::__construct();
        $this->files = new Collection();
    }

    public function files()
    {
        return $this->files;
    }

    public function getErrorMessage(ComponentInterface $component)
    {
        $params = ['message' => $component->getError()];
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

    public function render($name, $params = [], $language = null, $with_options = true) 
    {    
        $component = $this->create($name,'components',$language,$with_options);
        if ($component->hasError() == true) {
            return $component;
        }      
        $params['component_url'] = $component->getUrl();
        $this->includeFiles($component);
        
        $params = Arrays::merge($component->getProperties(),$params);
        $component->setHtmlCode("");  
        if ($component->getOption('render') !== false) {                 
            $component = $this->fetch($component,$params);
        }
        return $component;
    }

    public function includeFiles(ComponentInterface $component) 
    {
        // js file
        $js_files = array_column($component->getFiles('js'),'url');
        Arikaim::page()->properties()->merge('include.components.js',$js_files);
        Arikaim::view()->component()->files()->merge("js_files",$js_files);
       
        // css files
        $css_files = array_column($component->getFiles('css'),'url');
        Arikaim::page()->properties()->merge('include.components.css',$css_files);
        Arikaim::view()->component()->files()->merge("css_files",$css_files);          
    }
    
    public function getComponentProperties($name, $language = null)
    {
        $component = $this->create($name,'components',$language);
        return $this->loadComponentProperties($component);
    }

    public function getComponentDetails($name, $language = null)
    {
        $component = $this->create($name,'components',$language);
        
        $details['properties'] = $component->getProperties();
        $details['options'] = $component->getOptions();
        $details['files'] = $component->getFiles();
        $details['error'] = $component->getError();
        $details['template_name'] = $component->getTemplateName();

        return $details;
    }
}
