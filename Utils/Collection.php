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

/**
 * Collection base class
 */
class Collection implements CollectionInterface, \Countable, \ArrayAccess, \IteratorAggregate
{
    protected $data;
  
    public function __construct($data = []) 
    {  
        $this->data = $data;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    public function offsetExists($key)
    {
        return array_key_exists($key,$this->data);
    }

    public function offsetGet($key) 
    {
        return $this->data[$key];
    }

    public function offsetSet($key, $value)
    {
        $this->data[$key] = $value;
    }
    
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }

    public function count()
    {
        return count($this->data);
    }

    public function setBooleanValue($path, $value)
    {
        if (is_numeric($value) == true) {
            $value = (intval($value) > 0) ? true : false;
        }
        if (is_string($value) == true) {
            $value = ($value === "true") ? true : false;
        }
        $this->setValue($path,$value);
    }

    public function setValue($path,$value)
    {
        $this->data = Arrays::setValue($this->data,$path,$value);
    }

    public function merge($key, array $data)
    {
        if (isset($this->data[$key]) == false) {
            $this->data[$key] = [];
        }       
        $this->data[$key] = array_merge($this->data[$key],$data);
    }

    /**
     * Set item value in collection
     *
     * @param string $key Key Name
     * @param mixed $value Value
     * @return void
     */
    public function set($key, $value) 
    {
        $this->data[$key] = $value;
    }

    /**
     * Add item to collection
     *
     * @param string $key key name
     * @param mixed $value
     * @return void
     */
    public function add($key, $value) 
    {
        if (isset($this->data[$key]) == false) {
            $this->data[$key] = [];
        }       
        array_push($this->data[$key],$value);
        $this->data[$key] = array_values(array_unique($this->data[$key]));
    }
    
    /**
     * Return collection array 
     *
     * @return array
     */
    public function toArray()
    {
        return is_array($this->data) ? $this->data : [];
    }

    /**
     * Return true if key exists and value not empty in collection
     *
     * @param string $key Name
     * @return boolean
     */
    public function isEmpty($key)
    {
        return (isset($this->data[$key]) == false) ? true : empty($this->data[$key]);      
    }

    /**
     * Get value from collection
     *
     * @param string $key Name
     * @param mixed $default_value If key not exists return default value
     * @return mixed
     */
    public function get($key, $default_value = null)
    {      
        if (isset($this->data[$key]) == false) {
            return $default_value;
        }
        return ($this->data[$key] == null) ? $default_value : $this->data[$key];         
    }

    public function getArray($key, $default_value = null)
    {
        $result = $this->get($key,$default_value);
        return (is_array($result) == false) ? [] : $result;
    }

    /**
     * Clear collection data
     *
     * @return void
     */
    public function clear() 
    {
        $this->data = [];
    }

    /**
     * Clone object
     *
     * @return object
     */
    public function copy()     
    {
        return clone $this;
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key,$value)
    {
        return $this->set($key,$value);
    }

    public function getByPath($path, $default_value = null)
    {
        $value = Arrays::getValue($this->data,$path);
        return (empty($value) == true) ? $default_value : $value;
    }
    
    public function addField($path, $value)
    {
        foreach ($this->data as $key => $item) {
            if (is_array($item) == true) {
                $current_value = Arrays::getValue($item,$path);
                if ($current_value === null) {
                    $this->data[$key] = Arrays::setValue($item,$path,$value);
                }
            }
        }
        return true;
    }
}
