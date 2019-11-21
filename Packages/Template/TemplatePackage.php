<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Packages\Template;

use Arikaim\Core\Packages\Package;
use Arikaim\Core\Packages\Template\TemplateRepository;
use Arikaim\Core\Db\Model;
use Arikaim\Core\App\Factory;
use Arikaim\Core\Arikaim;
use Arikaim\Core\App\Path;
use Arikaim\Core\View\Template\Template;
use Arikaim\Core\View\Html\Component;
use Arikaim\Core\Collection\Interfaces\CollectionInterface;

/**
 * Template package 
*/
class TemplatePackage extends Package
{
    /**
     * Constructor
     *
     * @param CollectionInterface $properties
     */
    public function __construct(CollectionInterface $properties) 
    {
        parent::__construct($properties);

        $repositoryUrl = $properties->get('repository',null);
        $this->repository = new TemplateRepository($repositoryUrl);
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
        $routesCount = count($routes);

        // install template routes
        $routesAdded = 0;
      
        foreach ($routes as $route) {
            if (isset($route['path']) == false || isset($route['page']) == false) {             
                continue;
            }
          
            $handlerClass = Factory::getControllerClass("PageLoader"); 
            $result = $model->addTemplateRoute($route['path'], $handlerClass, 'loadPage', $this->getName(), $route['page']);
            if ($result != false) {
                $routesAdded++;
            }
        }
        if ($routesAdded != $routesCount) {
            return false;
        }
        // trigger core.template.install event
        Arikaim::event()->dispatch('core.template.install',$this->getProperties()->toArray());

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
        $result = Model::Routes()->deleteTemplateRoutes($this->getName());
        // trigger core.template.uninstall event
        Arikaim::event()->dispatch('core.template.uninstall',$this->getProperties()->toArray());

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
