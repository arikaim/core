<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Utils;

use Arikaim\Core\Access\Access;
use Arikaim\Core\Interfaces\ModuleInterface;
use Arikaim\Core\Interfaces\Jobs\JobInterface;
use Arikaim\Core\Interfaces\ExtensionInterface;

class Factory 
{
    public static function createInstance($full_class_name, $args = null)
    {
        if (class_exists($full_class_name) == false) {
            $full_class_name = Self::getFullClassName($full_class_name);
        }
        if (class_exists($full_class_name) == false) {
            return false;
        }
        if ($args != null) {            
            $instance = new $full_class_name(...$args);
        } else {
            $instance = new $full_class_name;
        }
        if (is_object($instance) ) {        
            return $instance;
        }
        return null;
    }

    public static function createModule($module_name, $class_name, $args = null)
    {
        $full_class_name = Self::getModuleClass($module_name,$class_name);
        $instance = Self::createInstance($full_class_name,$args);
        if ($instance instanceof ModuleInterface) {
            return $instance;
        }
        return null;
    }

    public static function createExtension($extension_name, $class_name, $args = null)
    {
        $full_class_name = Self::getExtensionClassName($extension_name,$class_name);  
        $instance = Self::createInstance($full_class_name,$args);       
        if ($instance instanceof ExtensionInterface) {           
            return $instance;
        }
        return null;
    }

    public static function createJob($class_name, $extension_name = null, $args = null)
    {
        $job_class = Self::getJobClassName($extension_name,$class_name);
        $job = Self::createInstance($job_class,$args);
        if ($job instanceof JobInterface) {
            $job->setExtensionName($extension_name);
            return $job;
        }
        return null;
    }

    public static function createAuthMiddleware($auth, $args = null)
    {
        switch ($auth) {
            case Access::AUTH_SESSION: {
                $class_name = "SessionAuthentication";
                break;
            }
            case Access::AUTH_JWT: {
                $class_name = "JwtAuthentication";
                break;
            }
            default: {
                return null;
            }
        }
        return Self::createInstance(Self::getMiddlewareClassName($class_name),$args);
    }

    public static function getFullClassName($class_name)
    {
        return  Self::getCoreNamespace() . "\\$class_name";
    }

    public static function getCoreNamespace()
    {
        return "Arikaim\\Core";
    }

    public static function getModulesNamespace()
    {
        return "Arikaim\\Modules";
    }

    public static function getModuleNamespace($module_name)
    {
        return Self::getModulesNamespace() . "\\" . ucfirst($module_name);
    }

    public static function getModuleClass($module_name,$base_class)
    {
        return Self::getModuleNamespace($module_name) ."\\$base_class";
    }

    public static function getMiddlewareClassName($class_name)
    {
        return Self::getMiddlewareNamespace($class_name) . "\\$class_name";
    }

    public static function getMiddlewareNamespace()
    {
        return Self::getCoreNamespace() . "\\Middleware";
    }
 
    public static function getExtensionControlerClass($extension_name, $base_class_name)
    {
        $extension_name = ucfirst($extension_name);
        return Self::getExtensionControlersNamespace($extension_name) . "\\" . $base_class_name;
    }

    public static function getExtensionControlersNamespace($extension_name)
    {
        return Self::getExtensionNamespace($extension_name) . "\\Controlers";
    }

    public static function getExtensionNamespace($extension_name) 
    {   
        $extension_name = ucfirst($extension_name);
        return Self::getExtensionsNamespace() . "\\$extension_name";
    }

    public static function getExtensionClassName($extension_name, $base_class_name)
    {
        return Self::getExtensionNamespace($extension_name) . "\\" . $base_class_name;
    }

    public static function getExtensionsNamespace()
    {
        return "Arikaim\\Extensions";
    }

    public static function getInterfacesNamespace()
    {
        return "Arikaim\\Core\\Interfaces";
    }

    public static function getFullInterfaceName($base_name)
    {
        return Self::getInterfacesNamespace() ."\\" . $base_name;
    }

    public static function getJobClassName($extension_name,$class_name)
    {
        return Self::getJobsNamespace($extension_name) . "\\$class_name";
    }

    public static function getJobsNamespace($extension_name = null)
    {
        if ($extension_name != null) {
            return Self::getExtensionNamespace($extension_name) . "\\Jobs";
        }
        return Self::getCoreNamespace() . "\\Jobs";
    }

    public static function getModelsNamespace()
    {
        return Self::getCoreNamespace() . "\\Models";
    }

    public static function getModelClass($class_name) 
    {
        return Self::getModelsNamespace() . "\\" . $class_name;
    }
    
    public static function getExtensionModelClass($extension_name, $base_class_name)
    {
        return Self::getExtensionModelNamespace($extension_name) . "\\" . $base_class_name;
    }

    public static function getExtensionModelNamespace($extension_name)
    {   
        return Self::getExtensionNamespace($extension_name) . "\\Models";
    }
}
