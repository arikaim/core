<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Db;

use Arikaim\Core\System\Factory;
use Arikaim\Core\Utils\FunctionArguments;

/**
 * Database Model Factory 
*/
class Model 
{   
    /**
     * Create db model instance
     *
     * @param string $className Base model class name
     * @param string $extensionName
     * @param Closure|null $callback
     * @return object|null
     */ 
    public static function create($className, $extensionName = null, $callback = null) 
    {  
        try {
            $className = (class_exists($className) == false) ? Self::getFullClassName($className,$extensionName) : $className; 
            $instance = Factory::createInstance($className);
            if (is_callable($callback) == true) {
                return $callback($instance);
            }
            return (Self::isValidModel($instance) == true) ? $instance : null;              
        } catch(\Exception $e) {           
        }
        return null;
    }

    /**
     * Return true if attribute exist
     *
     * @param string $name
     * @param Model $model
     * @return boolean
     */
    public static function hasAttribute($model, $name)
    {
        return array_key_exists($name, $model->attributes);
    }

    /**
     * Get model full calss name
     *
     * @param string $className
     * @param string|null $extensionName
     * @return string
     */
    public static function getFullClassName($className, $extensionName = null)
    {
        return (empty($extensionName) == true) ? Factory::getModelClass($className) : Factory::getExtensionModelClass($extensionName,$className);         
    }

    /**
     * Get sql 
     *
     * @param Builder|Model $builder
     * @return string
     */
    public static function getSql($builder)
    {
        $sql = str_replace(array('?'), array('\'%s\''),$builder->toSql());
        return vsprintf($sql,$builder->getBindings());     
    }

    /**
     * Get model constant
     *
     * @param string $className
     * @param string $constantName
     * @param string $extensionName
     * @return mixed
     */
    public static function getConstant($className, $constantName, $extensionName = null)
    {
        $className = Self::getFullClassName($className,$extensionName);
        return Factory::getConstant($className,$constantName);
    }

    /**
     * Create model
     *
     * @param string $name
     * @param array $args
     * @return object|null
     */
    public static function __callStatic($name, $args)
    {  
        $extensionName = (isset($args[0]) == true) ? $args[0] : null;
        $callback = (isset($args[1]) == true) ? $args[1] : null;

        return Self::create($name,$extensionName,$callback);
    }
    
    /**
     * Return true if instance is valid model class
     *
     * @param object $instance
     * @return boolean
     */
    public static function isValidModel($instance)
    {
        return is_subclass_of($instance,"\\Illuminate\\Database\\Eloquent\\Model");
    }
}
