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

class TemplateFunction  
{
    private $allowed_classes;
    private $deny_methods;

    public function __construct() 
    {
        $this->allowed_classes = [];
        $this->deny_methods = [];
        $this->allowClass(Factory::getCoreNamespace() . '\\Extension\\ExtensionsManager');     
        $this->allowClass(Factory::getCoreNamespace() . '\\Db\\Search');
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
            if ( is_array($array[$key]) == true) {               
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
    
    private function loadModelData($class_name, $extension_name = null, $condition = null, $order_by = null, $paginate = false, $search = false) 
    {           
        $model = Model::create($class_name,$extension_name);   
        if ($model == null) {
            return [];
        }    
        
        if ($search != false) {
            $search_conditions = Model::getSearchConditions($model,$search);
            $model = Model::applyCondition($model,$search_conditions);
        }   

        $model = Model::applyCondition($model,$condition);
              
        if ($order_by != null) {
            $model = $model->orderByRaw($order_by);
        } 
        
        if ($paginate == true) {           
            $model = $model->paginate(Paginator::getRowsPerPage(),['*'], 'page',Paginator::getCurrentPage());
            if (is_object($model) == false) {
                return [];
            }
            $model = $model->toArray(); 
            $result['paginator']['total'] = $model['total'];
            $result['paginator']['per_page'] = $model['per_page'];
            $result['paginator']['current_page'] = $model['current_page'];
            $result['paginator']['prev_page'] = Paginator::getPrevPage();
            $result['paginator']['next_page'] = Paginator::getNextPage($model['last_page']);
            $result['paginator']['last_page'] = $model['last_page'];
            $result['paginator']['from'] = $model['from'];
            $result['paginator']['to'] = $model['to'];
            $result['rows'] = $model['data'];            
            return $result;            
        } 
        
        $model = $model->get();
        if (is_object($model) == true) {
            return $model->toArray(); 
        }
        return [];
    }

    public function searchData($model_class_name, $order_by = null, $paginate = false)
    {
        return $this->loadModelData($model_class_name,null,null,$order_by,$paginate,true);
    }

    public function searchExtensionData($model_class_name, $extension_name, $order_by = null, $paginate = false)
    {
        return $this->loadModelData($model_class_name,$extension_name,null,$order_by,$paginate,true);
    }

    public function loadData($model_class_name, $condition = null, $order_by = null, $paginate = false, $search = null) 
    {     
        return $this->loadModelData($model_class_name,null,$condition,$order_by,$paginate,$search);
    }

    public function loadExtensionData($model_class_name, $extension_name, $condition = null, $order_by = null, $paginate = false, $search = null) 
    {    
        return $this->loadModelData($model_class_name,$extension_name,$condition,$order_by,$paginate,$search);
    }

    private function loadModelDataRow($model_class_name, $condition, $extension_name = null)
    {            
        $model = Model::create($model_class_name,$extension_name);
        if ($model == null) {
            return [];
        }                   
        $model = Model::applyCondition($model,$condition);

        $data = $model->first();
        if ($data != null) {
            return $data->toArray();
        }
        return [];
    }

    public function loadDataRow($model_class_name, $condition)
    {
        return $this->loadModelDataRow($model_class_name,$condition);
    }

    public function loadExtensionDataRow($model_class_name, $extension_name, $condition)
    {
        return $this->loadModelDataRow($model_class_name,$condition,$extension_name);
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
        if ($value == null) {
            $value = "";
        }
        return $value;
    }

    public function createCondition($field_name, $operator, $value, array $conditions = null)
    {
        $condition = Model::createCondition($field_name,$operator,$value,$conditions);
        return $condition->toArray();
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
