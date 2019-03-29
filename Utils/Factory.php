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
use Arikaim\Core\Interfaces\ProviderInterface;
use Arikaim\Core\Interfaces\Jobs\JobInterface;
use Arikaim\Core\Interfaces\ExtensionInterface;
use Arikaim\Core\System\Path;

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
        $instance = ($args != null) ? new $full_class_name(...$args) : new $full_class_name();           
           
        return ((is_object($instance) == true) ? $instance : null);                
    }

    public static function createSchema($class_name, $extension_name = null)
    {
        $schema_class_name = Self::getSchemaClass($class_name,$extension_name);
        $instance = Self::createInstance($schema_class_name);

        if (is_subclass_of($instance,Path::CORE_NAMESPACE . "\\Db\\Schema") == false) {
            throw new \Exception("Not valid schema class '$schema_class_name'");
            return null;           
        } 
        return $instance;
    }

    public static function getConstant($class_name,$name)
    {
        return constant($class_name . "::" . $name);
    }

    public static function createModule($module_name, $class_name, $args = null)
    {
        $full_class_name = Self::getModuleClass($module_name,$class_name);      
        $instance = Self::createInstance($full_class_name,$args);       

        return (($instance instanceof ModuleInterface) ? $instance : null);
    }

    public static function createExtension($extension_name, $class_name, $args = null)
    {
        $full_class_name = Self::getExtensionClassName($extension_name,$class_name);  
        $instance = Self::createInstance($full_class_name,$args);       

        return (($instance instanceof ExtensionInterface) ? $instance : null);                 
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

    public static function getEventSubscriberClass($base_class_name, $extension_name = null)
    {
        if (empty($extension_name) == true) {
            $class_name = Self::getSystemEventsNamespace() . "\\" . $base_class_name;
        } else {
            $class_name = Self::getExtensionEventSubscriberClass($base_class_name,$extension_name);
        }   
        return $class_name;
    }

    public static function createEventSubscriber($base_class_name, $extension_name = null)
    {        
        $class_name = Self::getEventSubscriberClass($base_class_name,$extension_name);         
        $instance = Self::createInstance($class_name);
        if ($instance instanceof EventSubscriberInterface) {  
            return $instance;
        }
        return false;
    }

    public static function getFullClassName($class_name)
    {
        return Path::CORE_NAMESPACE . "\\$class_name";
    }

    public static function getModuleNamespace($module_name)
    {
        return Path::MODULES_NAMESAPCE . "\\" . ucfirst($module_name);
    }

    public static function getModuleClass($module_name,$base_class)
    {
        return Self::getModuleNamespace($module_name) . "\\$base_class";
    }

    public static function getMiddlewareClassName($class_name)
    {
        return Path::MIDDLEWARE_NAMESPACE . "\\$class_name";
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

    public static function getExtensionEventSubscriberClass($base_class_name, $extension_name)
    {
        return Self::getExtensionEventsNamespace($extension_name) . "\\" . $base_class_name;
    }

    public static function getExtensionNamespace($extension_name) 
    {          
        return Path::EXTENSIONS_NAMESPACE . "\\" . ucfirst($extension_name);
    }

    public static function getExtensionClassName($extension_name, $base_class_name)
    {
        return Self::getExtensionNamespace($extension_name) . "\\" . $base_class_name;
    }

    public static function getExtensionConsoleClassName($extension_name, $base_class_name)
    {
        return Self::getExtensionNamespace($extension_name) . "\\Console\\$base_class_name";
    }

    public static function getFullInterfaceName($base_name)
    {
        return Path::INTERFACES_NAMESPACE ."\\" . $base_name;
    }

    public static function getJobClassName($extension_name,$class_name)
    {
        return Self::getJobsNamespace($extension_name) . "\\$class_name";
    }

    public static function getJobsNamespace($extension_name = null)
    {
        if (empty($extension_name) == false) {
            return Self::getExtensionNamespace($extension_name) . "\\Jobs";
        }
        return Path::CORE_NAMESPACE . "\\Jobs";
    }

    public static function getModelClass($class_name) 
    {
        return Path::CORE_NAMESPACE . "\\Models\\" . $class_name;
    }
    
    public static function getExtensionModelClass($extension_name, $base_class_name)
    {
        return Self::getExtensionModelNamespace($extension_name) . "\\" . $base_class_name;
    }

    public static function getExtensionModelNamespace($extension_name)
    {   
        return Self::getExtensionNamespace($extension_name) . "\\Models";
    }

    public static function getControlerClass($class_name)
    {
        return Path::CONTROLERS_NAMESPACE . "\\" . $class_name;
    }

    public static function getValidatorRuleClass($base_class)
    {
        return Path::CORE_NAMESPACE . "\\Validator\\Rule\\" . $base_class;
    }

    public static function getValidatorFiltersClass($base_class)
    {
        return Path::CORE_NAMESPACE . "\\Validator\\Filter\\" . $base_class; 
    }

    public static function getSystemEventsNamespace()
    {
        return Path::CORE_NAMESPACE . "\\Events";
    }

    public static function getExtensionEventsNamespace($extension_name)
    {
        return Self::getExtensionNamespace($extension_name) . "\\Events";
    }

    public static function getSchemaNamespace($extension_name = null)
    {
        if ($extension_name != null) {
            $extension_name = ucfirst($extension_name);
            return Path::EXTENSIONS_NAMESPACE . "\\$extension_name\\Models\\Schema\\";
        }
        return Path::CORE_NAMESPACE . "\\Models\\Schema\\";
    }

    public static function getSchemaClass($base_class, $extension_name)
    {
        return Self::getSchemaNamespace($extension_name) . $base_class;
    }
}
