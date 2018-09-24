<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Module;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Form\Properties;
use Arikaim\Core\FileSystem\File;

/**
 * Manage core modules
 */
class ModulesManager 
{
    public function __construct()
    {
    }

    /**
     * Install core medules
     *
     * @return int
     */
    public function install()
    {
        $path = Arikaim::getModulesPath();
        $modules = [];
        if (File::exists($path) == false) {
            return $modules;
        }

        foreach (new \DirectoryIterator($path) as $file) {
            if ($file->isDot() == true) continue;
            if ($file->isDir() == true) {
                $path = $file->getFilename();
                $module = $this->getModuleDetails($path,true);
                if (is_array($module) == true) {
                    array_push($modules,$module);
                }
            }
        }   
        // save to options
        Arikaim::options()->set('core.modules',$modules,true);
        return $modules;
    }

    public function getModuleDetails($path, $full_details = true)
    {
        $properties_file = $this->getModulePropertiesFileName($path);
        $properties = new Properties($properties_file);
        $default_class = ucfirst($path);
        $module['class'] = $properties->get('class',$default_class);
        $module['path'] = $path;

        $service = Factory::createModule($path,$module['class']);
        if (is_object($service) == true) {
            $module['service_name'] = $properties->get('service_name',$service->getServiceName());
            $module['bootable'] = $properties->get('bootable',$service->isBootable());
            // test funciton
            $module['status'] = $service->test();
            if ($full_details == true) {
                $module['version'] = $properties->get('version',$service->getVersion());
                $module['title'] = $properties->get('title',$service->getTitle());
                $module['description'] = $properties->get('description',$service->getDescription());
                $module['requires'] = $properties->get('requires');
                $module['help'] = $properties->get('help',null);
                $module['facade'] = $properties->get('facade',null);
            }
            return $module;
        }
        return null;
    }

    public function getModulesList()
    {
        $result = [];
        $modules = Arikaim::options()->get('core.modules');
        $modules = json_decode($modules,true);
        if (is_array($modules) == false) {
            return $result;
        }
       
        foreach ($modules as $item) {
            $module = $this->getModuleDetails($item['path']);
            array_push($result,$module);
        }
        return $result;
    }

    public function getModulePropertiesFileName($path)
    {
        return Arikaim::getModulesPath() . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . "module.json";
    }
}
