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

use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\Collection;

class ComponentsProperties extends Collection
{  
    private $include_files;

    public function __construct() 
    {
        parent::__construct();
        $this->include_files = [];
    }
    
    public function getIncludeFiles($key) 
    {
        if (isset($this->include_files[$key]) == true) {
            return $this->include_files[$key];
        }
        return false;
    }

    public function addIncludeFile($key,$value) 
    {
        if (isset($this->include_files[$key]) == false) {
            $this->include_files[$key] = [];
        }
        array_push($this->include_files[$key],$value);
        $this->include_files[$key] = array_unique($this->include_files[$key]);
    }

    public function getParam($component_name,$param_name) 
    {
        if (isset($this->data[$component_name]) == false) {
            return false;
        }        
        if (array_key_exists($param_name,$this->data[$component_name]) == true)  {
            return $this->data[$component_name][$param_name];
        }
        return false;
    }
            
    public function getProperties($component_name) 
    {
        if (array_key_exists($component_name,$this->data) == false)  {
            $this->data[$component_name] = [$component_name];
        } 
        return $this->data[$component_name];
    }

    public function addParam($component_name,$param_name,$value) 
    {   
        if (isset($this->data[$component_name][$param_name]) == false) {
            $this->data[$component_name][$param_name] = [];
        }
        array_push($this->data[$component_name][$param_name],$value);
        $this->data[$component_name][$param_name] = array_unique($this->data[$component_name][$param_name]);
    }

    public function setParam($component_name,$param_name,$value) 
    {
        $this->data[$component_name][$param_name] = $value;      
    }

    public function getComponentParam($component_name, $path) 
    {    
        $result = Utils::arrayGetValue($this->components->getParams($component_name),$path);
        return $result;   
    }
 
    public function setComponentParam($component_name,$param_path,$value) 
    {
        $result = Utils::arraySetValue($this->getProperties($component_name),$param_path,$value);   
        $this->setParams($component_name,$result);
        return $result;
    }
}
