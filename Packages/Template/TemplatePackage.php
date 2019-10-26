<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Packages\Template;

use Arikaim\Core\Packages\Package;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Arikaim;
use Arikaim\Core\System\Path;
use Arikaim\Core\View\Template\Template;
use Arikaim\Core\View\Html\Component;

/**
 * Template package 
*/
class TemplatePackage extends Package
{
    /**
     * Constructor
     *
     * @param \Arikaim\Core\Interfaces\Collection\CollectionInterface $properties
     */
    public function __construct($properties) 
    {
        parent::__construct($properties);
    }

    /**
     * Get package properties
     *
     * @param boolean $full
     * @return Collection
     */
    public function getProperties($full = false)
    {
        if ($full == true) {
            $this->properties->set('components',$this->getComponents());
            $this->properties->set('macros',$this->getMacros());
            $this->properties->set('pages',$this->getPages());
            $this->properties->set('routes',$this->getRoutes());
        }
        return $this->properties; 
    }

    /**
     * Install template package
     *
     * @return bool
     */
    public function install()
    {
        // clear cache
        Arikaim::cache()->clear();

        $result = Arikaim::options()->set('current.template',$this->getName());
        if ($result == false) {
            return false;
        }
        $model = Model::Routes();
        // delete all template routes
        $model->deleteTemplateRoutes('*');

        $routes = $this->getRoutes();
        $routes_count = count($routes);

        // install template routes
        $routes_added = 0;
      
        foreach ($routes as $route) {
            if (isset($route['path']) == false || isset($route['page']) == false) {             
                continue;
            }
          
            $handler_class = Factory::getControllerClass("PageLoader"); 
            $result = $model->addTemplateRoute($route['path'], $handler_class, 'loadPage', $this->getName(), $route['page']);
            if ($result != false) {
                $routes_added++;
            }
        }
        if ($routes_added != $routes_count) {
            return false;
        }
        // trigger core.template.install event
        Arikaim::event()->trigger('core.template.install',$this->getProperties()->toArray());
        return true;
    }
    
    /**
     * Uninstall package
     *
     * @return bool
     */
    public function unInstall() 
    {
        // clear cached items
        Arikaim::cache()->deleteTemplateItems();
        
        $model = Model::Routes();
        $result = $model->deleteTemplateRoutes($this->getName());
        // trigger core.template.uninstall event
        Arikaim::event()->trigger('core.template.uninstall',$this->getProperties()->toArray());
        return $result;
    }

    /**
     * Enable package
     *
     * @return bool
     */
    public function enable() 
    {
        // clear cached items
        Arikaim::cache()->deleteTemplateItems();
        return true;
    }

    /**
     * Disable package
     *
     * @return bool
     */
    public function disable() 
    {
        // clear cached items
        Arikaim::cache()->deleteTemplateItems();
        return true;
    }   

    /**
     * Get template routes
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->properties->getByPath('routes',[]);
    }

    /**
     * Get template pages
     *
     * @return array
     */
    public function getPages()
    {
        $path = Path::getPagesPath($this->getName(),Component::TEMPLATE_COMPONENT);    
        return Template::getPages($path);
    }

    /**
     * Get template macros
     *
     * @return array
     */
    public function getMacros()
    {
        $path = Path::getMacrosPath($this->getName(),Component::TEMPLATE_COMPONENT);
        return Template::getMacros($path);
    }

    /**
     * Get template components
     *
     * @return array
     */
    public function getComponents()
    {          
        $path = Path::getComponentsPath($this->getName(),Component::TEMPLATE_COMPONENT);    
        return Template::getComponents($path);
    }
}
