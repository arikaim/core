<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\App;

use Arikaim\Core\App\Path;

use Arikaim\Core\Interfaces\Job\JobInterface;
use Arikaim\Core\Events\Interfaces\EventSubscriberInterface;
use Arikaim\Core\Interfaces\ExtensionInterface;

/**
 * Object factory 
 */
class Factory 
{
    /**
     * Create object
     *
     * @param string $class
     * @param array|null $args
     * @return object|null
     */
    public static function createInstance($class, $args = null)
    {
        if (class_exists($class) == false) {
            return null;
        }       

        $instance = ($args != null) ? new $class(...$args) : new $class();           
           
        return (is_object($instance) == true) ? $instance : null;                
    }

    /**
     * Create validator rule
     *
     * @param string $name
     * @param array|null $args
     * @return Arikaim\Core\Validator\Interfaces\RuleInterface
     */
    public static function createRule($name, $args = null)
    {              
        $class = ucfirst($name);
        return Self::createInstance(Self::getValidatorRuleClass($class),$args);            
    }

    /**
     * Create db schema object
     *
     * @param string $schemaClass
     * @param string $extension
     * @return object|null
     */
    public static function createSchema($schemaClass, $extension = null)
    {
        $schemaClass = Self::getSchemaClass($schemaClass,$extension);
        $instance = Self::createInstance($schemaClass);

        if (is_subclass_of($instance,Path::CORE_NAMESPACE . "\\Db\\Schema") == false) {
            throw new \Exception("Not valid schema class '$schemaClass' ");
            return null;           
        } 

        return $instance;
    }

    /**
     * Get class constant
     *
     * @param string $class
     * @param string $name
     * @return mixed
     */
    public static function getConstant($class,$name)
    {
        return constant($class . "::" . $name);
    }

    /**
     * Create module object
     *
     * @param string $module
     * @param string $class
     * @param array $args
     * @return object|null
     */
    public static function createModule($module, $class, $args = null)
    {
        $moduleClass = Self::getModuleClass($module,$class);
      
        return  Self::createInstance($moduleClass,$args);             
    }

    /**
     * Create extension
     *
     * @param string $extension
     * @param string $class
     * @param array $args
     * @return object|null
     */
    public static function createExtension($extension, $class, $args = null)
    {
        $class = Self::getExtensionClassName($extension,$class);  
        $instance = Self::createInstance($class,$args);       

        return ($instance instanceof ExtensionInterface) ? $instance : null;                 
    }

    /**
     * Create Job
     *
     * @param string $class
     * @param string|null $extension
     * @param string|null $name
     * @param integer $priority
     * @return object|null
     */
    public static function createJob($class, $extension = null, $name = null)
    {  
        if (class_exists($class) == false) {
            $class = Self::getJobClassName($extension,$class);
        }
        $params = [$extension,$name];
        $job = Self::createInstance($class,$params);
       
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
     * @param string $baseClass
     * @param string|null $extension
     * @return string
     */
    public static function getEventSubscriberClass($baseClass, $extension = null)
    {
        if (empty($extension) == true) {
            return Self::getSystemEventsNamespace() . "\\" . $baseClass;
        } 
        
        return Self::getExtensionEventSubscriberClass($baseClass,$extension);        
    }

    /**
     * Create event subscriber
     *
     * @param string $baseClass
     * @param string|null $extension
     * @return object|null
     */
    public static function createEventSubscriber($baseClass, $extension = null)
    {        
        $class = Self::getEventSubscriberClass($baseClass,$extension);         
        $instance = Self::createInstance($class);
        
        return ($instance instanceof EventSubscriberInterface) ? $instance : null;         
    }

    /**
     * Get full core class name
     *
     * @param string $class
     * @return string
     */
    public static function getFullClassName($class)
    {
        return Path::CORE_NAMESPACE . "\\$class";
    }

    /**
     * Get module namespace
     *
     * @param string $module
     * @return string
     */
    public static function getModuleNamespace($module)
    {
        return Path::MODULES_NAMESAPCE . "\\" . ucfirst($module);
    }

    /**
     * Get module full class name
     *
     * @param string $module
     * @param string $baseClass
     * @return string
     */
    public static function getModuleClass($module, $baseClass)
    {
        return Self::getModuleNamespace($module) . "\\$baseClass";
    }

    /**
     * Get middleware full class name
     *
     * @param string $class
     * @return string
     */
    public static function getMiddlewareClassName($class)
    {
        return Path::MIDDLEWARE_NAMESPACE . "\\$class";
    }

    /**
     * Create middleware instance
     *
     * @param string $class
     * @param mixed|null $args
     * @return object|null
     */
    public static function createMiddleware($class, $args = null)
    {
        return Self::createInstance(Self::getMiddlewareClassName($class),$args);
    }

    /**
     * Create auth provider instance
     *
     * @param string $class
     * @param mixed|null $args
     * @return object|null
     */
    public static function createAuthProvider($class, $args = null)
    {
        return Self::createInstance(Path::ACCESS_NAMESPACE . "\\$class",$args);
    }

    /**
     * Get extension controller full class name
     *
     * @param string $extension
     * @param string $baseClass
     * @return string
     */
    public static function getExtensionControllerClass($extension, $baseClass)
    {        
        return Self::getExtensionControllersNamespace(ucfirst($extension)) . "\\" . $baseClass;
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
     * @param string $extension
     * @return string
     */
    public static function getExtensionControllersNamespace($extension)
    {
        return Self::getExtensionNamespace($extension) . "\\Controllers";
    }

    /**
     * Get extension subscriber full class name
     *
     * @param string $baseClass
     * @param string|null $extension
     * @return string
     */
    public static function getExtensionEventSubscriberClass($baseClass, $extension)
    {
        return Self::getExtensionSubscribersNamespace($extension) . "\\" . $baseClass;
    }

    /**
     * Get extension namespace
     *
     * @param string $extension
     * @return string
     */
    public static function getExtensionNamespace($extension) 
    {          
        return Path::EXTENSIONS_NAMESPACE . "\\" . ucfirst($extension);
    }

    /**
     * Get extension full class name
     *
     * @param string $extension
     * @param string $baseClass
     * @return string
     */
    public static function getExtensionClassName($extension, $baseClass)
    {
        return Self::getExtensionNamespace($extension) . "\\" . $baseClass;
    }

    /**
     * Get module console command full class name
     *
     * @param string $module
     * @param string $baseClass
     * @return string
     */
    public static function getModuleConsoleClassName($module, $baseClass)
    {
        return Self::getModuleNamespace($module) . "\\Console\\$baseClass";
    }

    /**
     * Get extension console command full class name
     *
     * @param string $extension
     * @param string $baseClass
     * @return string
     */
    public static function getExtensionConsoleClassName($extension, $baseClass)
    {
        return Self::getExtensionNamespace($extension) . "\\Console\\$baseClass";
    }

    /**
     * Get full interface name
     *
     * @param string $baseName
     * @return string
     */
    public static function getFullInterfaceName($baseName)
    {
        return Path::INTERFACES_NAMESPACE ."\\" . $baseName;
    }

    /**
     * Get job full class name
     *
     * @param string $extension
     * @param string $class
     * @return string
     */
    public static function getJobClassName($extension, $class)
    {
        return Self::getJobsNamespace($extension) . "\\$class";
    }

    /**
     * Get job namespace
     *
     * @param string|null $extension
     * @return string
     */
    public static function getJobsNamespace($extension = null)
    {
        if (empty($extension) == false) {
            return Self::getExtensionNamespace($extension) . "\\Jobs";
        }

        return Path::CORE_NAMESPACE . "\\Jobs";
    }

    /**
     * Get model full class name
     *
     * @param string $class
     * @return string
     */
    public static function getModelClass($class, $extension = null) 
    {
        if (empty($extension) == true) {
            return Path::CORE_NAMESPACE . "\\Models\\" . $class;
        }
    
        return Self::getExtensionModelNamespace($extension) . "\\" . $class;
    }
    
    /**
     * Get extension namespace
     *
     * @param string $extension
     * @return string
     */
    public static function getExtensionModelNamespace($extension)
    {   
        return Self::getExtensionNamespace($extension) . "\\Models";
    }

    /**
     * Get controller full class name
     *
     * @param string $class
     * @return string
     */
    public static function getControllerClass($class)
    {
        return Path::CONTROLLERS_NAMESPACE . "\\" . $class;
    }

    /**
     * Get validator rule full class name
     *
     * @param string $baseClass
     * @return string
     */
    public static function getValidatorRuleClass($baseClass)
    {
        $class = Path::CORE_NAMESPACE . "\\Validator\\Rule\\" . $baseClass;
        if (class_exists($class) == false) {
            $class = Path::CORE_NAMESPACE . "\\Validator\\Rule\\Db\\" . $baseClass;
        }

        return $class;
    }

    /**
     * Get validator filter full class name
     *
     * @param string $baseClass
     * @return string
     */
    public static function getValidatorFiltersClass($baseClass)
    {
        return Path::CORE_NAMESPACE . "\\Validator\\Filter\\" . $baseClass; 
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
     * @param string $extension
     * @return string
     */
    public static function getExtensionSubscribersNamespace($extension)
    {
        return Self::getExtensionNamespace($extension) . "\\Subscribers";
    }

    /**
     * Get db schema namespace
     *
     * @param string|null $extension
     * @return string
     */
    public static function getSchemaNamespace($extension = null)
    {
        if ($extension != null) {
            $extension = ucfirst($extension);
            return Path::EXTENSIONS_NAMESPACE . "\\$extension\\Models\\Schema\\";
        }
        
        return Path::CORE_NAMESPACE . "\\Models\\Schema\\";
    }

    /**
     * Get db schema class
     *
     * @param string $baseClass
     * @param string $extension
     * @return string
     */
    public static function getSchemaClass($baseClass, $extension)
    {
        return Self::getSchemaNamespace($extension) . $baseClass;
    }
}
