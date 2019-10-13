<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Traits\Db;

/**
 * Options table trait
*/
trait Options 
{    
    /**
     * Read option
     *
     * @param integer $reference_id
     * @param string $key     
     * @param mixed $default
     * @return mixed
     */
    public function read($reference_id, $key, $default = null) 
    {
        $model = $this->findOption($reference_id,$key);
        return (is_object($model) == false) ? $default : $model->value;                      
    }

    /**
     * Return true if option name exist
     *
     * @param integer $reference_id
     * @param string $key
     * @return boolean
     */
    public function hasOption($reference_id, $key)
    {
        $model = $this->findOption($reference_id,$key);
        return is_object($model);
    }

    /**
     * Fidn option
     *
     * @param integer $reference_id
     * @param string $key
     * @return Model|null
     */
    public function findOption($reference_id, $key)
    {
        return $this->where('reference_id','=',$reference_id)->where('key','=',$key)->first();
    }

    /**
     * Save option
     *
     * @param integer $reference_id
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function set($reference_id, $key, $value) 
    {
        $key = trim($key);
        if (empty($key) == true) {
            return false;
        }
    
        if (is_array($value) == true) {            
            $value = Utils::jsonEncode($value,true);           
        }

        $data = [
            'reference_id' => $reference_id,
            'key'          => $key,
            'value'        => $value
        ];      
        $model = $this->findOption($reference_id,$key);  
        $result = (is_object($model) == true) ? $model->update($data) : $this->create($data);             
    
        return $result;
    }

    /**
     * Create option, if option exists return false
     *
     * @param integer $reference_id
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public function createOption($reference_id, $key, $value)
    {
        return ($this->hasOption($reference_id,$key) == true) ? false : $this->set($reference_id,$key,$value);       
    }
}
