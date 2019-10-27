<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Utils;

use Arikaim\Core\System\Path;

use Arikaim\Core\Interfaces\Queue\JobInterface;
use Arikaim\Core\Interfaces\Events\EventSubscriberInterface;
use Arikaim\Core\Interfaces\ExtensionInterface;

/**
 * Object factory 
 */
class Factory 
{
    /**
     * Create object
     *
     * @param string $full_class_name
     * @param array|null $args
     * @return object|null
     */
    public static function createInstance($full_class_name, $args = null)
    {
        if (class_exists($full_class_name) == false) {
            $full_class_name = Self::getFullClassName($full_class_name);
        }
       
        if (class_exists($full_class_name) == false) {
            return false;
        }
        $instance = ($args != null) ? new $full_class_name(...$args) : new $full_class_name();           
           
        return (is_object($instance) == true) ? $instance : null;                
    }

    /**
     * Create validator rule
     *
     * @param string $name
     * @param array|null $args
     * @return Arikaim\Core\Interfaces\RuleInterface
     */
    public static function createRule($name, $args = null)
    {              
        $class_name = ucfirst($name);
        return Self::createInstance(Self::getValidatorRuleClass($class_name),$args);            
    }

    /**
     * Create db schema object
     *
     * @param string $class_name
     * @param string $extension_name
     * @return object|null
     */
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

    /**
     * Get class constant
     *
     * @param string $class_name
     * @param string $name
     * @return mixed
     */
    public static function getConstant($class_name,$name)
    {
        return constant($class_name . "::" . $name);
    }

    /**
     * Create module object
     *
     * @param string $module_name
     * @param string$class_name
     * @param array $args
     * @return object|null
     */
    public static function createModule($module_name, $class_name, $args = null)
    {
        $full_class_name = Self::getModuleClass($module_name,$class_name);
        return  Self::createInstance($full_class_name,$args);             
    }

    /**
     * Create extension
     *
     * @param string $extension_name
     * @param string $class_name
     * @param array $args
     * @return object|null
     */
    public static function createExtension($extension_name, $class_name, $args = null)
    {
        $full_class_name = Self::getExtensionClassName($extension_name,$class_name);  
        $instance = Self::createInstance($full_class_name,$args);       

        return ($instance instanceof ExtensionInterface) ? $instance : null;                 
    }

    /**
     * Create Job
     *
     * @param string $class_name
     * @param string|null $extension_name
     * @param string|null $name
     * @param integer $priority
     * @return object|null
     */
    public static function createJob($class_name, $extension_name = null, $name = null)
    {  
        if (class_exists($class_name) == false) {
            $class_name = Self::getJobClassName($extension_name,$class_name);
        }
        $params = [$extension_name,$name];
        $job = Self::createInstance($class_name,$params);
       
        return ($job instanceof JobInterface) ? $job : null;
    }

    /**
     * Create job intence from array 
     *
     * @param array $data
     * @param string|null $class
     * @return object|null
     */
    public static function createJobFromArray(array $data, $class = null)
    {
        if (empty($class) == true) {
            $class = $data['class'];
        }

        $instance = Self::createJob($class);
        if ($instance == null) {
            return null;
        }

        foreach ($data as $key => $value) {
            $instance->{$key} = $value;
        }

        return $instance;
    }

    /**
     * Get event subscriber full class name
     *
     * @param string $base_class_name
     * @param string|null $extension_name
     * @return string
     */
    public static function getEventSubscriberClass($base_class_name, $extension_name = null)
    {
        if (empty($extension_name) == true) {
            $class_name = Self::getSystemEventsNamespace() . "\\" . $base_class_name;
        } else {
            $class_name = Self::getExtensionEventSubscriberClass($base_class_name,$extension_name);
        }   
        return $class_name;
    }

    /**
     * Create event subscriber
     *
     * @param string $base_class_name
     * @param string|null $extension_name
     * @return object|null
     */
    public static function createEventSubscriber($base_class_name, $extension_name = null)
    {        
        $class_name = Self::getEventSubscriberClass($base_class_name,$extension_name);         
        $instance = Self::createInstance($class_name);
        
        return ($instance instanceof EventSubscriberInterface) ? $instance : null;         
    }

    /**
     * Get full core class name
     *
     * @param string $class_name
     * @return string
     */
    public static function getFullClassName($class_name)
    {
        return Path::CORE_NAMESPACE . "\\$class_name";
    }

    /**
     * Get module namespace
     *
     * @param string $module_name
     * @return string
     */
    public static function getModuleNamespace($module_name)
    {
        return Path::MODULES_NAMESAPCE . "\\" . ucfirst($module_name);
    }

    /**
     * Get module full class name
     *
     * @param string $module_name
     * @param string $base_class
     * @return string
     */
    public static function getModuleClass($module_name,$base_class)
    {
        return Self::getModuleNamespace($module_name) . "\\$base_class";
    }

    /**
     * Get middleware full class name
     *
     * @param string $class_name
     * @return string
     */
    public static function getMiddlewareClassName($class_name)
    {
        return Path::MIDDLEWARE_NAMESPACE . "\\$class_name";
    }

    /**
     * Create middleware instance
     *
     * @param string $class_name
     * @param mixed|null $args
     * @return object|null
     */
    public static function createMiddleware($class_name, $args = null)
    {
        return Self::createInstance(Self::getMiddlewareClassName($class_name),$args);
    }

    /**
     * Create auth provider instance
     *
     * @param string $class_name
     * @param mixed|null $args
     * @return object|null
     */
    public static function createAuthProvider($class_name, $args = null)
    {
        return Self::createInstance(Path::ACCESS_NAMESPACE . "\\$class_name",$args);
    }

    /**
     * Get extension controller full class name
     *
     * @param string $extension_name
     * @param string $base_class_name
     * @return string
     */
    public static function getExtensionControllerClass($extension_name, $base_class_name)
    {        
        return Self::getExtensionControllersNamespace(ucfirst($extension_name)) . "\\" . $base_class_name;
    }

    /**
     * Return true if class is core contoler class
     *
     * @param string $class
     * @return boolean
     */
    public static function isCoreControllerClass($class)
    {
        return (substr($class,0,7) == 'Arikaim');
    }
    
    /**
     * Get extension controller namespace
     *
     * @param string $extension_name
     * @return string
     */
    public static function getExtensionControllersNamespace($extension_name)
    {
        return Self::getExtensionNamespace($extension_name) . "\\Controllers";
    }

    /**
     * Get extension subscriber full class name
     *
     * @param string $base_class_name
     * @param string|null $extension_name
     * @return string
     */
    public static function getExtensionEventSubscriberClass($base_class_name, $extension_name)
    {
        return Self::getExtensionSubscribersNamespace($extension_name) . "\\" . $base_class_name;
    }

    /**
     * Get extension namespace
     *
     * @param string $extension_name
     * @return string
     */
    public static function getExtensionNamespace($extension_name) 
    {          
        return Path::EXTENSIONS_NAMESPACE . "\\" . ucfirst($extension_name);
    }

    /**
     * Get extension full class name
     *
     * @param string $extension_name
     * @param string $base_class_name
     * @return string
     */
    public static function getExtensionClassName($extension_name, $base_class_name)
    {
        return Self::getExtensionNamespace($extension_name) . "\\" . $base_class_name;
    }

    /**
     * Get module console command full class name
     *
     * @param string $module_name
     * @param string $base_class_name
     * @return string
     */
    public static function getModuleConsoleClassName($module_name, $base_class_name)
    {
        return Self::getModuleNamespace($module_name) . "\\Console\\$base_class_name";
    }

    /**
     * Get extension console command full class name
     *
     * @param string $extension_name
     * @param string $base_class_name
     * @return string
     */
    public static function getExtensionConsoleClassName($extension_name, $base_class_name)
    {
        return Self::getExtensionNamespace($extension_name) . "\\Console\\$base_class_name";
    }

    /**
     * Get full interface name
     *
     * @param string $base_name
     * @return string
     */
    public static function getFullInterfaceName($base_name)
    {
        return Path::INTERFACES_NAMESPACE ."\\" . $base_name;
    }

    /**
     * Get job full class name
     *
     * @param string $extension_name
     * @param string $class_name
     * @return string
     */
    public static function getJobClassName($extension_name,$class_name)
    {
        return Self::getJobsNamespace($extension_name) . "\\$class_name";
    }

    /**
     * Get job namespace
     *
     * @param string|null $extension_name
     * @return string
     */
    public static function getJobsNamespace($extension_name = null)
    {
        if (empty($extension_name) == false) {
            return Self::getExtensionNamespace($extension_name) . "\\Jobs";
        }
        return Path::CORE_NAMESPACE . "\\Jobs";
    }

    /**
     * Get model full class name
     *
     * @param string $class_name
     * @return string
     */
    public static function getModelClass($class_name) 
    {
        return Path::CORE_NAMESPACE . "\\Models\\" . $class_name;
    }
    
    /**
     * Get extension model full class name
     *
     * @param string $extension_name
     * @param string $base_class_name
     * @return string
     */
    public static function getExtensionModelClass($extension_name, $base_class_name)
    {
        return Self::getExtensionModelNamespace($extension_name) . "\\" . $base_class_name;
    }

    /**
     * Get extension namespace
     *
     * @param string $extension_name
     * @return string
     */
    public static function getExtensionModelNamespace($extension_name)
    {   
        return Self::getExtensionNamespace($extension_name) . "\\Models";
    }

    /**
     * Get controller full class name
     *
     * @param string $class_name
     * @return string
     */
    public static function getControllerClass($class_name)
    {
        return Path::CONTROLLERS_NAMESPACE . "\\" . $class_name;
    }

    /**
     * Get validator rule full class name
     *
     * @param string $base_class
     * @return string
     */
    public static function getValidatorRuleClass($base_class)
    {
        $class = Path::CORE_NAMESPACE . "\\Validator\\Rule\\" . $base_class;
        if (class_exists($class) == false) {
            $class = Path::CORE_NAMESPACE . "\\Validator\\Rule\\Db\\" . $base_class;
        }
        return $class;
    }

    /**
     * Get validator filter full class name
     *
     * @param string $base_class
     * @return string
     */
    public static function getValidatorFiltersClass($base_class)
    {
        return Path::CORE_NAMESPACE . "\\Validator\\Filter\\" . $base_class; 
    }

    /**
     * Get system events namespace
     *
     * @return string
     */
    public static function getSystemEventsNamespace()
    {
        return Path::CORE_NAMESPACE . "\\Events";
    }

    /**
     * Get extension event subscribers namespace
     *
     * @param string $extension_name
     * @return string
     */
    public static function getExtensionSubscribersNamespace($extension_name)
    {
        return Self::getExtensionNamespace($extension_name) . "\\Subscribers";
    }

    /**
     * Get db schema namespace
     *
     * @param string|null $extension_name
     * @return string
     */
    public static function getSchemaNamespace($extension_name = null)
    {
        if ($extension_name != null) {
            $extension_name = ucfirst($extension_name);
            return Path::EXTENSIONS_NAMESPACE . "\\$extension_name\\Models\\Schema\\";
        }
        return Path::CORE_NAMESPACE . "\\Models\\Schema\\";
    }

    /**
     * Get db schema class
     *
     * @param string $base_class
     * @param string $extension_name
     * @return string
     */
    public static function getSchemaClass($base_class, $extension_name)
    {
        return Self::getSchemaNamespace($extension_name) . $base_class;
    }

    /**
     * Get class name from obj ref
     *
     * @param object $obj
     * @return string
     */
    public static function getClassName($obj) 
    {    
        $current_class = get_class($obj);
        $class = new \ReflectionClass($current_class);      
        
        return last(explode("\\",$class->getNamespaceName()));       
    }
}
