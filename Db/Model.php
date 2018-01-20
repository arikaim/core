<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Db;

use Illuminate\Database\Capsule\Manager;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Utils\FunctionArguments;
use Arikaim\Core\Db\Schema;

class Model 
{    
    public static function create($class_name, $extension_name = null) 
    {  
        $full_class_name = empty($extension_name) ? Model::getModelClass($class_name) : Model::getExtensionModelClass($extension_name,$class_name);
        $instance = Factory::createInstance($full_class_name);
        return $instance;
    }

    public static function __callStatic($name, $args)
    {  
        $extension_name = FunctionArguments::getArgument($args,0,"string");
        $create_table = FunctionArguments::getArgument($args,0,"boolean");        
        $instance = Self::create($name,$extension_name);    
        if ($instance == null) {
            throw new \Exception(Arikaim::getError("DB_MODEL_CLASS_NOT_EXIST",['class' => $name]));
            return null;
        }   
        if ($create_table == true) {      
            if (Schema::hasTable($instance) == false) {
                $schema_class = Schema::getModelSchemaClass($name);
                Schema::install($schema_class,$extension_name);
            }
        }
        return $instance;     
    }
    
    public static function isValidModel($instance)
    {
        return is_subclass_of($instance,"\\Illuminate\\Database\\Eloquent\\Model");
    }

    public static function getModelClass($base_class_name) {
        return Model::getModelsNamespace() . $base_class_name;
    }

    public static function getExtensionModelClass($extension_name, $base_class_name)
    {
        return Model::getExtensionModelNamespace($extension_name) . "\\" . $base_class_name;
    }

    public static function getExtensionModelNamespace($extension_name)
    {   
        $extension_name = ucfirst($extension_name);
        return "\\Arikaim\\Extensions\\$extension_name\\Models";
    }

    public static function getModelsNamespace()
    {
        return "\\Arikaim\\Core\\Models\\";
    }
}
