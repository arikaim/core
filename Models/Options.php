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
  
    public function read($key) 
    {
        try {
            $model = $this->where('key','=',$key)->first();
            if (is_object($model) == false) {
                return null;
            }
            return $model->value;                        
        } catch(\Exception $e) {
            return null;
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
        $ooptions = Arikaim::cache()->fetch('options');
        if (is_array($ooptions) == true) {
            $this->options = $ooptions;
            return true;
        }
        try {
            $model = $this->where('auto_load','=','1')->select('key','value')->get();
            if (is_object($model) == true) {
                $this->options = $model->mapWithKeys(function ($item) {
                    return [$item['key'] => $item['value']];
                })->toArray();
                Arikaim::cache()->save('options',$this->options,2);
                return true;
            }               
        } catch(\Exception $e) {

        }
        return false;
    }

    private function setOption($key,$value)
    {
        $this->options[$key] = $value;
    } 

    public function get($key, $default_value = null)
    {
        if (isset($this->options[$key]) == true) {
            return $this->options[$key];
        }
        $value = $this->read($key);
        if ($value == null) {
            if ($default_value != null) {
                return $default_value;
            }
            return null;
        }       
        if (Utils::isJSON($value) == true) {            
            $value = json_decode($value,true);           
        }
        return $value;
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
