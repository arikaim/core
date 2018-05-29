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
class Collection implements \Iterator,CollectionInterface
{
    protected $data;
    protected $position;

    public function __construct() 
    {
        $this->clear();
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
        if (isset($this->data[$key]) == false) {
            return true;
        }
        return empty($this->data[$key]);
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
            return ($default_value !== null) ? $default_value : null;
        }
        if ($this->data[$key] == null) {
            return ($default_value !== null) ? $default_value : null;
        }    
        return $this->data[$key];            
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
        $this->position = 0;
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
}
