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

use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Db\Paginator;
use Arikaim\Core\Db\Search;

class TemplateFunction  
{
    private $allowed_classes;
    private $deny_methods;

    public function __construct() 
    {
        $this->allowed_classes = [];
        $this->deny_methods = [];
        $this->allowClass(Factory::getCoreNamespace() . 'Extension\\ExtensionsManager');     
        $this->allowClass(Factory::getCoreNamespace() . 'Db\\Search');
        $this->allowClass(Factory::getCoreNamespace() . 'Extension\\Routes');           
        $this->allowClass(Factory::getCoreNamespace() . 'View\\TemplatesManager');
        $this->allowClass(Factory::getCoreNamespace() . 'View\\UiLibrary');
        $this->allowClass(Factory::getCoreNamespace() . 'Install\\Install');
        $this->allowClass(Factory::getCoreNamespace() . 'Logger\\SystemLogger');
        $this->allowClass(Factory::getCoreNamespace() . 'System');
    }

    public function execute($function_name, $params = null) 
    {
        foreach ($this->allowed_classes as $key => $class_name) {
            $result = $this->executeMethod($class_name,$function_name,$params);
            if ($result != null) return $result;
        }
        return null;
    }

    public function executeMethod($full_class_name, $method_name, $params = null) 
    {
        if ($this->isAllowed($full_class_name) == false) {
            $vars['class_name'] = $full_class_name;
            return Arikaim::getError("NOT_ALLOWED_METHOD_ERROR",$vars);
        }
        $obj = Factory::createInstance($full_class_name);       
        return $this->callMethod($full_class_name,$obj,$method_name,$params);       
    }

    public function isAllowedMethod($method_name)
    {
        if (in_array($method_name,$this->deny_methods) == true ) return false;
        return true;
    }

    public function isAllowed($class_name) 
    {   
        if (in_array($class_name,$this->allowed_classes) == true) return true;
        if (in_array(Factory::getCoreNamespace() . $class_name,$this->allowed_classes) == true) return true;
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
    
    private function loadModelData($full_model_class_name, $where = null, $order_by = null, $paginate = false,$search = false) 
    {           
        $this->allowClass($full_model_class_name);
        $model = Factory::createInstance($full_model_class_name);
        if ($model == null) {
            return [];
        }
        if ($where != null) {
            $items = $model->whereRaw($where);
        } else {
            $items = $model;
        }

        if ($search != false) {
            $items = Search::search($items,$search);
        }
       // exit();
        if ($order_by != null) {
            $items = $items->orderByRaw($order_by);
        } 
      
        if ($paginate == true) {           
            $items = $items->paginate(Paginator::getRowsPerPage(),['*'], 'page',Paginator::getCurrentPage());
            if (is_object($items) == false) return [];
            $items = $items->toArray(); 
            
            $result['paginator']['total'] = $items['total'];
            $result['paginator']['per_page'] = $items['per_page'];
            $result['paginator']['current_page'] = $items['current_page'];
            $result['paginator']['prev_page'] = Paginator::getPrevPage();
            $result['paginator']['next_page'] = Paginator::getNextPage($items['last_page']);
            $result['paginator']['last_page'] = $items['last_page'];
            $result['paginator']['from'] = $items['from'];
            $result['paginator']['to'] = $items['to'];
            $result['rows'] = $items['data'];
            return $result;
        } else {
            $items = $items->get();
        }
       
        if (is_object($items) == true) {
            return $items->toArray(); 
        }
        return [];
    }

    public function loadData($model_class_name, $where = null, $order_by = null, $paginate = false, $search = null) 
    {     
        return $this->loadModelData(Model::getModelClass($model_class_name),$where,$order_by,$paginate,$search);
    }

    public function loadExtensionData($model_class_name, $extension_name = "", $where = null, $order_by = null, $paginate = false, $search = null) 
    {    
        return $this->loadModelData(Model::getExtensionModelClass($extension_name,$model_class_name),$where,$order_by,$paginate,$search);
    }

    private function loadModelDataRow($full_model_class_name, $keys)
    {      
        $this->allowClass($full_model_class_name);
        $model = Factory::createInstance($full_model_class_name);
        if ($model == null) return [];

        foreach ($keys as $key => $value) {
           $model = $model->where($key,'=',$value);
        }
        $data = $model->first();
        if ($data != null) {
            return $data->toArray();
        }
        return [];
    }

    public function loadDataRow($model_class_name, $keys)
    {
        return $this->loadModelDataRow(Model::getModelClass($model_class_name),$keys);
    }

    public function loadExtensionDataRow($model_class_name, $extension_name, $keys)
    {
        return $this->loadModelDataRow(Model::getExtensionModelClass($extension_name,$model_class_name),$keys);
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
        $language = Arikaim::getLanguage();
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
        $full_class_name = empty($extension_name) ? Model::getModelClass($class_name) : Model::getExtensionModelClass($extension_name,$class_name);
        $this->allowClass($full_class_name);
        $model = Model::create($class_name,$extension_name);    
        if ($method_name != null) {          
            return $this->callMethod($full_class_name,$model,$method_name,$args);
        }
        return $model;
    }
    
    private function callStaticMethod($class_name, $method_name, $params)
    {
        $callable = "$class_name::$method_name";
        if (is_callable($callable) == true) {
            return call_user_func($callable,$params);     
        }
        return null;
    }

    private function callMethod($full_class_name, $obj, $method_name, $params = null) 
    {
        if ($this->isAllowedMethod($method_name) == false) {
            return null;
        }
        if (is_object($obj) == false) return null;
        $function_var = array($obj, $method_name);
        if (method_exists($obj,$method_name) == false) return null;

        if (is_callable($function_var,true,$method_name) == true) {             
            return call_user_func($function_var,$params);          
        }
        return $this->callStaticMethod($full_class_name,$method_name,$params);
    }
}
