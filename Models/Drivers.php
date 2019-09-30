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
use Arikaim\Core\Traits\Db\Uuid;
use Arikaim\Core\Traits\Db\Find;
use Arikaim\Core\Traits\Db\Status;

use Arikaim\Core\Db\Model as dbModel;
/**
 * Drivers registry database model
 */
class Drivers extends Model  
{
    use Uuid,
        Find,
        Status;

    /**
     * Fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        'class',
        'name',
        'title',
        'class',
        'config',
        'version',
        'description',
        'category',
        'extension_name',
        'module_name',
        'config'
    ];
    
    /**
     * Timestamps fields disabled
     *
     * @var boolean
    */
    public $timestamps = false;
    
    /**
     * Db table name
     *
     * @var string
     */
    protected $table = 'drivers';

    /**
     * Mutator (set) for config attribute.
     *
     * @param array $value
     * @return void
     */
    public function setConfigAttribute($value)
    {
        $value = (is_array($value) == true) ? $value : [$value];    
        $this->attributes['config'] = json_encode($value);
    }

    /**
     * Mutator (get) for config attribute.
     *
     * @return array
     */
    public function getConfigAttribute()
    {
        return (empty($this->attributes['config']) == true) ? [] : json_decode($this->attributes['config'],true);
    }

    /**
     * Mutator (get) for full_name attribute.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return (empty($this->category) == false) ? $this->name . ":" . $this->category : $this->name;
    }

    /**
     * Get driver
     *
     * @param string|integer $name Driver name, id or uuid 
     * @param string|null $category
     * @param boolean $get_query
     * @return Model|boolean
     */
    public function getDriver($name, $category = null, $get_query = false)
    {
        $model = $this->findQuery($name,['name','id','uuid']);
        if (empty($category) == false) {
            $model = $model->where('category','=',$category);
        }  
        if (is_object($model) == true) {
            return ($get_query == true) ? $model : $model->first();
        }
        
        return false;
    }

    /**
     * Set driver status
     *
     * @param string|integer $name Driver name, id or uuid 
     * @param string|null $category
     * @param integer $status
     * @return boolean
     */
    public function setStatus($name, $status, $category = null)
    {
        $model = $this->getDriver($name,$category);
        if (is_object($model) == true) {
            $model->status = $status;
            $model->save();
            return true;
        }
        return false;
    }

    /**
     * Delete driver
     *
     * @param string|integer $name Driver name, id or uuid 
     * @param string|null $category
     * @return boolean
     */
    public function remove($name, $category = null)
    {
        $model = $this->getDriver($name,$category,true);
        return (is_object($model) == true) ? $model->delete() : true;
    }

    /**
     * Return true if driver is exist
     *
     * @param string|integer $name Driver name, id or uuid 
     * @param string|null $category
     * @return boolean
     */
    public function has($name, $category = null)
    {           
        return is_object($this->getDriver($name,$category));
    }

    /**
     * Delete extension drivers
     *
     * @param string $extension_name
     * @return boolean
     */
    public function deleteExtensionDrivers($extension_name)
    {
        $model = $this->where('extension_name','=',$extension_name);       
        return $model->delete();
    }

    /**
     * Delete module drivers
     *
     * @param string $module_name
     * @return boolean
     */
    public function deleteModuleDrivers($module_name)
    {
        $model = $this->where($module_name,'=',$module_name);       
        return $model->delete();
    }

    /**
     * Add or update driver
     *    
     * @param array $info
     * @return Model|boolean
     */
    public function add(array $info)
    {             
        $model = $this->getDriver($info['name'],$info['category']);
    
        return (is_object($model) == true) ? $model->update($info) : $this->create($info);
    }   
}
