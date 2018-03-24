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

use Arikaim\Core\Interfaces\CollectionInterface;
use Arikaim\Core\Utils\Arrays;

class Collection implements \Iterator,CollectionInterface
{
    protected $data;
    protected $position;

    public function __construct() 
    {
        $this->clear();
    }

    public function setValue($path,$value)
    {
        $this->data = Arrays::setValue($this->data,$path,$value);
    }

    public function set($key, $value) 
    {
        $this->data[$key] = $value;
    }

    public function add($key, $value) 
    {
        if (isset($this->data[$key]) == false) {
            $this->data[$key] = [];
        }       
        array_push($this->data[$key],$value);
        $this->data[$key] = array_values(array_unique($this->data[$key]));
        return true;
    }
    
    public function current()
    {
        return $this->data[$this->position];
    }
    
    public function key()
    {
        return $this->position;
    }
    
    public function next()
    {
        $this->position++;
    }
    
    public function rewind()
    {
        $this->position = 0;
    }
    
    public function valid()
    {
        return isset($this->data[$this->position]);
    }
    
    public function toArray()
    {
        return is_array($this->data) ? $this->data : [];
    }

    public function isEmpty($key)
    {
        if (isset($this->data[$key]) == false) {
            return true;
        }
        return empty($this->data[$key]);
    }

    public function get($key, $default_value = null)
    {      
        if (($default_value != null) && ($this->isEmpty($key) == true)) {
            return $default_value;
        }      
        if (isset($this->data[$key]) == true) {
            return $this->data[$key];      
        }
        return null;
    }

    public function getArray($key, $default_value = null)
    {
        $result = $this->get($key,$default_value);
        if (is_array($result) == false) {
            return [];
        }
        return $result;
    }

    public function clear() 
    {
        $this->data = [];
        $this->position = 0;
    }

    public function copy()     
    {
        return clone $this;
    }
}
