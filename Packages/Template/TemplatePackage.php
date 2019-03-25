<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Packages\Template;

use Arikaim\Core\Packages\Package;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Arikaim;
use Arikaim\Core\System\Path;
use Arikaim\Core\FileSystem\File;
use Arikaim\Core\View\Template;

/**
 * Template Package class
*/
class TemplatePackage extends Package
{
    //protected $porperties_list = ['path','name','title','description','version','requires','image','properties','default-theme','themes','current','',''];
    
    public function __construct($properties) 
    {
        parent::__construct($properties);
    }

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

    public function install()
    {
        // clear cached items
        Arikaim::cache()->deleteTemplateItems();

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
            if (isset($route['path']) == false) {
                // missing path
                continue;
            }
            if (isset($route['page']) == false) {
                // missing page
                continue;
            }
            $template_page = $route['page'];
            $handler_method = "loadTemplatePage";
            $handler_class = Factory::getControlerClass("Pages\\PageLoader"); 
            
            $result = $model->addTemplateRoute($route['path'], $handler_class, $handler_method, $this->getName(), $template_page);
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

    public function enable() 
    {
        // clear cached items
        Arikaim::cache()->deleteTemplateItems();

        return true;
    }

    public function disable() 
    {
        // clear cached items
        Arikaim::cache()->deleteTemplateItems();
        
        return true;
    }   

    public function getRoutes()
    {
        return $this->properties->getByPath('routes',[]);
    }

    public function getPages()
    {
        $path = Path::getPagesPath($this->getName());    
        return Template::getPages($path);
    }

    public function getMacros()
    {
        $path = Path::getMacrosPath($this->getName());
        return Template::getMacros($path);
    }

    public function getComponents()
    {       
        $path = Path::getComponentsPath($this->getName());
        return Template::getComponents($path);
    }
}
