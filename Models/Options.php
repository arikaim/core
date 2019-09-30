<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Models;

use Illuminate\Database\Eloquent\Model;

use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\Traits\Db\Find;
use Arikaim\Core\Arikaim;

/**
 * Options database model
 */
class Options extends Model  
{    
    use Find;

    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Fillable attributes
     *
     * @var array
    */
    protected $fillable = [
        'key',
        'value',
        'auto_load',
        'extension'
    ];
    
    /**
     * Options loaded.
     *
     * @var array
     */
    private $options = [];
    
    /**
     * Read option
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function read($key, $default = null) 
    {
        try {
            $model = $this->where('key','=',$key)->first();
            return (is_object($model) == false) ? $default : $model->value;                      
        } catch(\Exception $e) {
        }
        return $default;
    }

    /**
     * Create option, if option exists return false
     *
     * @param string $key
     * @param mixed $value
     * @param boolean $auto_load
     * @param string|null $extension_name
     * @return boolean
     */
    public function createOption($key, $value, $auto_load = false, $extension_name = null)
    {
        return ($this->hasOption($key) == true) ? false : $this->set($key,$value,$auto_load,$extension_name);       
    }

    /**
     * Return true if option name exist
     *
     * @param string $key
     * @return boolean
     */
    public function hasOption($key)
    {
        $model = $this->findByColumn($key,'key');
        return is_object($model);
    }

    /**
     * Save option
     *
     * @param string $key
     * @param mixed $value
     * @param boolean $auto_load
     * @param string $extension_name
     * @return bool
     */
    public function set($key, $value, $auto_load = false, $extension_name = null) 
    {
        $key = trim($key);
        if (empty($key) == true) {
            return false;
        }
        $key = str_replace('_','.',$key);
        
        if (is_array($value) == true) {            
            $value = Utils::jsonEncode($value,true);           
        }

        $data = [
            'key'       => $key,
            'value'     => $value,
            'auto_load' => ($auto_load == true) ? 1 : 0,      
            'extension' => $extension_name
        ];
    
        try {
            // clear options cache
            Arikaim::cache()->delete('options');

            $model = $this->findByColumn($key,'key');  
            $result = (is_object($model) == true) ? $model->update($data) : $this->create($data);             
        } catch(\Exception $e) {
            return false;
        }
        $this->setOption($key,$value);

        return $result;
    }

    /**
     * Load options
     *
     * @return Model
     */
    public function loadOptions()
    {
        $options = Arikaim::cache()->fetch('options');
        if (is_array($options) == true) {
            $this->options = $options;
            return $this;
        }
        try {
            $model = $this->where('auto_load','=','1')->select('key','value')->get();
            if (is_object($model) == true) {
                $this->options = $model->mapWithKeys(function ($item) {
                    return [$item['key'] => $item['value']];
                })->toArray();
                Arikaim::cache()->save('options',$this->options,2);
                return $this;
            }               
        } catch(\Exception $e) {

        }
        return $this;
    }

    /**
     * Set option
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    private function setOption($key,$value)
    {
        $this->options[$key] = $value;
    } 

    /**
     * Get option
     *
     * @param string $key
     * @param mixed $default_value
     * @return mixed
     */
    public function get($key, $default_value = null)
    {
        $value = (isset($this->options[$key]) == true) ? $this->options[$key] : $this->read($key,$default_value);
        return (Utils::isJSON($value) == true) ? json_decode($value,true) : $value;                      
    }   
    
    /**
     * Return loaded options
     *
     * @return array
     */
    public function getLoaded()
    {
        return $this->options;
    }
    
    /**
     * Search for options
     *
     * @param string $search_key
     * @return array
     */
    public function searchOptions($search_key)
    {
        $options = [];
        $model = $this->where('key','like',"$search_key%")->select('key','value')->get();
      
        if (is_object($model) == true) {
            $options = $model->mapWithKeys(function ($item) {
                return [$item['key'] => $item['value']];
            })->toArray(); 
        }     
        $values = Arrays::getValues($options,$search_key);
        if (is_array($values) == false) {
            return [];
        }
        $result = null;
        foreach ($values as $key => $value) {
            $result = Arrays::setValue($result,$key,$value,'.');
        }      
        return $result;      
    }

    /**
     * Remove option
     *
     * @param string $key
     * @return bool
     */
    public function remove($key)
    {
        $result = $this->where('key','=',$key)->delete();
        unset($this->options[$key]);
        return $result;
    }

    /**
     * Remove all extension options 
     *
     * @param string $extension_name
     * @param boolean $reload
     * @return bool
     */
    public function removeExtensionOptions($extension_name, $reload = true) 
    {
        $result = $this->where('extension','=',$extension_name)->delete();
        if ($reload == true) {
            $this->loadOptions();
        }
        return $result;
    }
}
