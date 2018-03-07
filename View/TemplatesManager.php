<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\View;

use Arikaim\Core\Form\Properties;
use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Template;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Controlers\Controler;

class TemplatesManager 
{
    private $template;

    public function __construct()
    {
        $this->template = new Template();
    }

    public function scan()
    {
        $items = [];
        $templates_path = Template::getTemplatesPath();
        foreach (new \DirectoryIterator($templates_path) as $file) {
            if ($file->isDot() == true) continue;
            if ($file->isDir() == true) {
                $template_name = $file->getFilename();      
                array_push($items,$template_name);                 
            }
        }  
        return $items;
    }

    public function getTemlateDetails($template_name)
    {   
        $properties = $this->template->loadProperties($template_name); 
       
        $details['path'] = Template::getTemplatePath($template_name);
        $details['properties'] = $properties->toArray();
        $details['name'] = $properties->get('name',$template_name);
        $details['title'] = $properties->get('title',$template_name);
        $details['description'] = $properties->get('description',"");
        $details['version'] = $properties->get('version','1.0'); 
        $details['themes'] = $properties->get('themes',[]); 
        $details['default-theme'] = $properties->get('default-theme',null); 
        $details['requires'] = $properties->get('include',[]); 
        $details['image'] = $properties->get('image',[]); 
        $details['current'] = $properties->get('current',false); 
        $details['components'] = $this->getTemplateComponents($template_name);
        $details['macros'] = $this->getTemplateMacros($template_name);
        $details['pages'] = $this->getTemplatePages($template_name);
        $details['routes'] = $this->getRoutesList($template_name);

        return $details;
    }

    public function getThemes($template_name = null)
    {
        $properties = $this->template->loadProperties($template_name); 
        return $properties->get('themes',[]); 
    }

    public static function getComponents($path, $parent_path = null)
    {
        $items = [];
        $root_path = $path;
        if ($parent_path != null) {
            $path .= $parent_path . DIRECTORY_SEPARATOR;
        }  

        foreach (new \DirectoryIterator($path) as $file) {
            if ($file->isDot() == true) continue;
            if ($file->isDir() == true) {
                $item['path'] = $file->getFilename();        
                if ($parent_path != null) {
                    $item['path'] = $parent_path . DIRECTORY_SEPARATOR . $item['path'];
                }
                $item['name'] = str_replace(DIRECTORY_SEPARATOR,'.',$item['path']);
                array_push($items,$item);

                // get child items
                $child_items = Self::getComponents($root_path, $item['path'] );
                if (count($child_items) > 0) {
                    $items = array_merge($items,$child_items);
                }
            }
        }
        return $items;
    }

    public function getTemplateComponents($template_name = null)
    {          
        $path = Template::getComponentsPath($template_name);
        return Self::getComponents($path);
    }

    public function getTemplateMacros($template_name)
    {
        $items = [];
        $path = Template::getMacrosPath($template_name);
        foreach (new \DirectoryIterator($path) as $file) {
            if (($file->isDot() == true) || ($file->isDir() == true)) continue;
            $file_ext = $file->getExtension();
            if (($file_ext != "html") && ($file_ext != "htm")) continue;           
            
            $item['name'] = str_replace(".$file_ext",'',$file->getFilename());
            array_push($items,$item);            
        }
        return $items;
    }

    public function getTemplatePages($template_name)
    {
        $items = [];
        $path = Template::getPagesPath($template_name);     
        foreach (new \DirectoryIterator($path) as $file) {
            if ($file->isDot() == true) continue;
            if ($file->isDir() == true) {
                $item['name'] = $file->getFilename();
                array_push($items,$item);
            }
        }
        return $items;
    }

    public function getRoutesList($template_name)
    {
        $model = Model::Routes();
        $routes = $this->getTemplateRoutes($template_name);
        if (is_array($routes) == false) {
            return [];
        }
        foreach ($routes as $key => $item) {
            $routes[$key]['method'] = "GET";
            $route = $model->getTemplateRoute('GET', $routes[$key]['path'],$template_name);
            if ($route != false) {
                $routes[$key]['status'] = $route->status;
                $routes[$key]['auth'] = $route->auth;
            } else {
                $routes[$key]['status'] = 0;
                $routes[$key]['auth'] = 0;
            }
        }
        return $routes;
    }

    public function getTemplateRoutes($template_name)
    {
        $template = new Template();
        $properties = $template->loadProperties($template_name);
        $routes = $properties->getByPath('routes',[]);
        return $routes;
    }

    public function install($template_name) 
    {       
        $result = Arikaim::options()->set('current.template',$template_name);
        if ($result == false) {
            return false;
        }
        $model = Model::Routes();
        $routes = $this->getTemplateRoutes($template_name);
        $routes_count = count($routes);

        // install template routes
        $routes_added = 0;
        foreach ($routes as $route) {
            if (isset($route['path']) == false) {
                // missing path value
                continue;
            }
            if (isset($route['page']) == false) {
                // missing templaet page
                continue;
            }
            $template_page = $route['page'];
            $handler_method = "loadTemplatePage";
            $handler_class = Controler::getControlerClass("Pages\\PageLoader");
            $pattern = "";
            $path = $route['path'];                     
            $result = $model->addTemplateRoute($path, $pattern, $handler_class, $handler_method, $template_name, $template_page);
            if ($result != false) {
                $routes_added++;
            }
        }
        if ($routes_added != $routes_count) {
            return false;
        }
        $details = $this->getTemlateDetails($template_name);
        // trigger core.template.install event
        Arikaim::event()->trigger('core.template.install',$details);
        return true;
    }
    
    public function uninstall($template_name)
    {
        $model = Model::Routes();
        $result = $model->deleteTemplateRoutes($template_name);

        $details = $this->getTemlateDetails($template_name);
        // trigger core.template.uninstall event
        Arikaim::event()->trigger('core.template.uninstall',$details);

        return $result;
    }
}
