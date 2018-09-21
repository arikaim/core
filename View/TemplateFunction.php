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

use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Db\Paginator;
use Arikaim\Core\Db\Search;
use Arikaim\Core\View\Template;
use Illuminate\Database\Capsule\Manager;

class TemplateFunction  
{
    private $allowed_classes;
    private $deny_methods;

    public function __construct() 
    {
        $this->allowed_classes = [];
        $this->deny_methods = [];
        $this->allowClass(Factory::getCoreNamespace() . '\\Extension\\ExtensionsManager');     
        $this->allowClass(Factory::getCoreNamespace() . '\\Db\\Paginator');
        $this->allowClass(Factory::getCoreNamespace() . '\\Extension\\Routes');           
        $this->allowClass(Factory::getCoreNamespace() . '\\View\\TemplatesManager');
        $this->allowClass(Factory::getCoreNamespace() . '\\View\\UiLibrary');
        $this->allowClass(Factory::getCoreNamespace() . '\\View\\Html\\HtmlComponent');
        $this->allowClass(Factory::getCoreNamespace() . '\\System\\Install');
        $this->allowClass(Factory::getCoreNamespace() . '\\System\\Update');
        $this->allowClass(Factory::getCoreNamespace() . '\\Access\\Access');
        $this->allowClass(Factory::getCoreNamespace() . '\\Utils\\Utils');
        $this->allowClass(Factory::getCoreNamespace() . '\\Logger\\SystemLogger');
        $this->allowClass(Factory::getCoreNamespace() . '\\System\\System');
        $this->allowClass(Factory::getCoreNamespace() . '\\Module\\ModulesManager');
    }

    public function service($service_name, $method_name, $params = null)
    {
        $service = Arikaim::$service_name();
        return $this->callMethod($service,$method_name,$params);       
    }

    public function callStatic($class_name, $method_name, $params = null)
    {
        if ($this->isAllowed($class_name) == false) {
            $vars['class_name'] = $class_name;
            return Arikaim::getError("NOT_ALLOWED_METHOD_ERROR",$vars);
        }
        $full_class_name = Factory::getFullClassName($class_name);      
        return Utils::callStatic($full_class_name,$method_name,$params);
    }

    public function hasExtension($extension_name)
    {
        $extension = Model::Extensions();
        $extension = $extension->where('name','=',$extension_name)->get();
        if (is_object($extension) == true) {
            return true;
        }
        return false;
    }

    public function extensionMethod($extension_name, $class_name, $method_name, $params = null)
    {
        $full_class_name = Factory::getExtensionClassName($extension_name,"Classes\\$class_name");
        $this->allowClass($full_class_name);
        return $this->executeMethod($full_class_name,$method_name,$params);
    }

    public function executeMethod($full_class_name, $method_name, $params = null) 
    {
        if ($this->isAllowed($full_class_name) == false) {
            $vars['class_name'] = $full_class_name;
            return Arikaim::getError("NOT_ALLOWED_METHOD_ERROR",$vars);
        }
       
        $obj = Factory::createInstance($full_class_name);       
        return $this->callMethod($obj,$method_name,$params);       
    }

    public function isAllowedMethod($method_name)
    {
        if (in_array($method_name,$this->deny_methods) == true ) {
            return false;
        }
        return true;
    }

    public function isAllowed($class_name) 
    {   
        if (in_array($class_name,$this->allowed_classes) == true) {
            return true;
        }
        if (in_array(Factory::getFullClassName($class_name),$this->allowed_classes) == true) {
            return true;
        }
        return false;
    }

    public function denyMethod($class_name, $method_name) 
    {
        if (is_array($this->deny_methods[$class_name]) == false) {
            $this->deny_methods[$class_name] = [];
        }
        if (in_array($method_name,$this->deny_methods[$class_name]) == false) {
            array_push($this->allowed_classes[$class_name], $method_name);
        }
    }

    public function allowClass($class_name) 
    {
        if (in_array($class_name,$this->allowed_classes) == false) {
            array_push($this->allowed_classes, $class_name);
        }
    }

    public function getFileType($file_name) 
    {
        return pathinfo($file_name, PATHINFO_EXTENSION);
    }

    public function haveSubItems($array)
    {
        if (is_array($array) == false) return false;
        foreach ($array as $key => $value) {        
            if (is_array($array[$key]) == true) {               
                return true;
            }
        }
        return false;
    } 

    public function currentYear()
    {
        return date("Y");
    }

    public function getHiddenClass($value, $hide_class = null)
    {
        if ($hide_class == null) { 
            $hide_class = "hidden";
        }
        if (($value == 0) || ($value == null)) {
            return $hide_class;
        }
        return "";
    }
    
    public function dbQuery($model_class_name, $query_builder = null, $extension_name = null, $paginate = false, $debug = false) 
    {           
        $model = Model::create($model_class_name,$extension_name);   
        if ($model == null) {
            return [];
        }    
        $model = Model::buildQuery($model,$query_builder);
        
        if ($debug == true) {
            echo Model::getSql($model);
        }
      
        if ($paginate == true) {   
            return Paginator::create($model);        
        } 
        $model = $model->get();

        if (is_object($model) == true) {
            return $model;
        }
        return false;
    }

    public function dbQueryPage($model_class_name, $query_builder = null, $extension_name = null, $debug = false)
    {
        return $this->dbQuery($model_class_name,$query_builder,$extension_name,true,$debug);
    }

    public function dbQueryRow($model_class_name, $query_builder = null, $extension_name = null, $debug = false)
    {
        $result = $this->dbQuery($model_class_name,$query_builder,$extension_name,false,$debug);
        return result;
        //if (isset($result[0]) == true) {
        //    return $result[0];
       // }
        //return [];
    }

    public function createModel($class_name, $method_name = null, $args = null)
    {
        return $this->createDBModel($class_name,null,$method_name,$args);
    }
    
    public function createExtensionModel($class_name, $extension_name, $method_name = null, $args = null)
    {
        return $this->createDBModel($class_name,$extension_name,$method_name,$args);
    }

    public function getCurrentLanguage() 
    {
        $language = Template::getLanguage();
        $model = Model::Language()->where('code','=',$language)->first();
        if (is_object($model) == true) {
            return $model->toArray();
        }
        return null;
    }

    public function getOption($name,$default_value = null) 
    {
        $value = Arikaim::options()->get($name,$default_value);   
        return ($value == null) ? "" : $value;
    }

    public function getOptions($search_key)
    {
        $options = Arikaim::options()->getOptions($search_key);   
        if ($options == null || $options == false) {
            $options = "";
        }
        return $options;
    }

    private function createDBModel($class_name, $extension_name = null, $method_name = null, $args = null)
    {
        $full_class_name = Model::getFullClassName($class_name, $extension_name);
        $this->allowClass($full_class_name);
        $model = Model::create($class_name,$extension_name);    
        if ($method_name != null) {   
            return $this->callMethod($model,$method_name,$args);
        }
        return $model;
    }
    
    private function callMethod($obj, $method_name, $params = null) 
    {
        if ($this->isAllowedMethod($method_name) == false) {
            return null;
        }
        return Utils::call($obj,$method_name,$params);
    }

    public function getComponentProperties($name, $language = null)
    {
        return Arikaim::view()->component()->getComponentProperties($name,$language)->toArray();
    }
}
