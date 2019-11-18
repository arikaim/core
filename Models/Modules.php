<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Models;

use Illuminate\Database\Eloquent\Model;

use Arikaim\Core\Traits\Db\Uuid;
use Arikaim\Core\Traits\Db\Status;
use Arikaim\Core\Traits\Db\Find;
use Arikaim\Core\Packages\Module\ModulePackage;

/**
 * Modules database model
 */
class Modules extends Model  
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
        'name',
        'title',
        'description',
        'short_description',
        'class',
        'type',
        'bootable',
        'status',
        'service_name',
        'version',     
        'console_commands',
        'facade_class',
        'facede_alias',
        'config',
        'category'
    ];
    
    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;
   
    /**
     * Mutator (set) for console_commands attribute.
     *
     * @param array|null $value
     * @return void
     */
    public function setConsoleCommandsAttribute($value)
    {
        $value = (is_array($value) == true) ? $value : [];         
        $this->attributes['console_commands'] = json_encode($value);
    }

    /**
     * Mutator (get) for console_commands attribute.
     *
     * @return array
     */
    public function getConsoleCommandsAttribute()
    {
        return (empty($this->attributes['console_commands']) == true) ? [] : json_decode($this->attributes['console_commands'],true);
    }

    /**
     * Mutator (set) for config attribute.
     *
     * @param array $value
     * @return void
     */
    public function setConfigAttribute($value)
    {
        $value = (is_array($value) == true) ? $value : [];    
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
     * Return true if module record exist.
     *
     * @param string $name
     * @return boolean
     */
    public function isInstalled($name)
    {
        $model = $this->where('name','=',$name); 
        return is_object($model->first());
    }

    /**
     * Get module status
     *
     * @param string $name
     * @return integer
     */
    public function getStatus($name)
    {
        $model = $this->where('name','=',$name)->first();       
        return (is_object($model) == false) ? 0 : $model->status;            
    }

    /**
     * Set module status
     *
     * @param string $name
     * @param integer $status
     * @return bool
     */
    public function setStatus($name, $status)
    {
        $model = $this->findByColumn($name,'name');
        return (is_object($model) == true) ? $model->update(['status' => $status]) : false;         
    }

    /**
     * Return modules list
     *
     * @param integer|string $type
     * @param integer $status
     * @return array
     */
    public function getList($type = null, $status = null)
    {          
        if (is_string($type) == true) {
            $type = ModulePackage::getTypeId($type);
        }
        $model = $this;
        if ($type !== null) {
            $model = $model->where('type','=',$type);
        }  
        if ($status !== null) {
            $model = $model->where('status','=',$status);
        }
        $model = $model->get();
        
        return (is_object($model) == true) ? $model->toArray() : [];
    }

    /**
     * Disable module
     *
     * @param string $name
     * @return bool
     */
    public function disable($name)
    {        
        return $this->setStatus($name,Status::$DISABLED);
    }

    /**
     * Enable module
     *
     * @param string $name
     * @return bool
     */
    public function enable($name)
    {
        return $this->setStatus($name,Status::$ACTIVE);
    }
}
