<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Db;

use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Utils\FunctionArguments;

/**
 * Database Model Factory 
*/
class Model 
{   
    /**
     * Create db model instance
     *
     * @param string $class_name Base model class name
     * @param string $extension_name
     * @param Closure|null $callback
     * @return object|null
     */ 
    public static function create($class_name, $extension_name = null, $callback = null) 
    {  
        try {
            $full_class_name = (class_exists($class_name) == false) ? Self::getFullClassName($class_name,$extension_name) : $class_name; 
            $instance = Factory::createInstance($full_class_name);
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
     * @param string $class_name
     * @param string|null $extension_name
     * @return string
     */
    public static function getFullClassName($class_name, $extension_name = null)
    {
        if (empty($extension_name) == true) {
            return Factory::getModelClass($class_name);
        }
        return Factory::getExtensionModelClass($extension_name,$class_name);
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
     * @param string $class_name
     * @param string $constant_name
     * @param string $extension_name
     * @return mixed
     */
    public static function getConstant($class_name, $constant_name, $extension_name = null)
    {
        $class_name = Self::getFullClassName($class_name,$extension_name);
        return Factory::getConstant($class_name,$constant_name);
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
        $extension_name = (isset($args[0]) == true) ? $args[0] : null;
        $callback = (isset($args[1]) == true) ? $args[1] : null;

        return Self::create($name,$extension_name,$callback);
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
