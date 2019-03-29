<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Traits\Db\Find;
use Arikaim\Core\Arikaim;

/**
 * Options database model
 */
class Options extends Model  
{    
    use Find;

    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'auto_load',
        'extension'];
        
    private $options = [];
  
    public function read($key, $default_value = null) 
    {
        try {
            $model = $this->where('key','=',$key)->first();
            return (is_object($model) == false) ? $default_value : $model->value;                      
        } catch(\Exception $e) {
            return $default_value;
        }
    }

    public function set($key, $value, $auto_load = true, $extension_name = null) 
    {
        if (trim($key) == "") {
            return false;
        }
        $key = str_replace('_','.',$key);
        
        if (is_array($value) == true) {            
            $value = Utils::jsonEncode($value,true);           
        }
        $data['key'] = $key;
        $data['value'] = $value;
        $data['auto_load'] = ($auto_load == true) ? 1 : 0;
        if ($extension_name != null) {
            $data['extension'] = $extension_name;
        }
        $option = $this->hasOption($key);

        if ($option == false) {
            $result = $this->create($data);
        } else {
            $result = $option->update($data);
        }
        $this->setOption($key,$value);
        return $result;
    }

    public function hasOption($key)
    {
        try {
            return $this->findByColumn($key,'key');          
        } catch(\Exception $e) {
            return false;
        }
    }

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

    private function setOption($key,$value)
    {
        $this->options[$key] = $value;
    } 

    public function get($key, $default_value = null)
    {
        $value = (isset($this->options[$key]) == true) ? $this->options[$key] : $this->read($key,$default_value);
        return (Utils::isJSON($value) == true) ? json_decode($value,true) : $value;                      
    }   
    
    public function getLoaded()
    {
        return $this->options;
    }
    
    public function getOptions($search_key)
    {
        $result = null;
        $values = Arrays::getValues($this->options,$search_key);
        if (is_array($values) == false) {
            return $result;
        }
        foreach ($values as $key => $value) {
            $result = Arrays::setValue($result,$key,$value,'.');
        }      
        return $result;      
    }

    public function remove($key)
    {
        $result = $this->where('key','=',$key)->delete();
        unset($this->options[$key]);
        return $result;
    }

    public function removeExtensionOptions($extension_name, $reload = true) 
    {
        $result = $this->where('extension','=',$extension_name)->delete();
        if ($reload == true) {
            $this->loadOptions();
        }
        return $result;
    }
}
