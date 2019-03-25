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
use Arikaim\Core\Traits\Db\ToggleValue;
use Arikaim\Core\Traits\Db\Position;
use Arikaim\Core\Traits\Db\Status;
use Arikaim\Core\Traits\Db\Find;
use Arikaim\Core\Db\Schema;

/**
 * Modules database model
 */
class Modules extends Model  
{
    use Uuid,
        Find,
        ToggleValue,
        Status;

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
        'facede_alias'];
    
    public $timestamps = false;
   
    public function setConsoleCommandsAttribute($value)
    {
        $value = (is_array($value) == true)  ? json_encode($value) : '';         
        $this->attributes['console_commands'] = $value;
    }

    public function getConsoleCommandsAttribute()
    {
        return json_decode($this->attributes['console_commands']);
    }

    public function isInstalled($name)
    {
        $model = $this->where('name','=',$name)->first();       
        return (is_object($model) == true) ? true : false;   
    }

    public function getStatus($name)
    {
        $model = $this->where('name','=',$name)->first();       
        return (is_object($model) == false) ? 0 : $model->status;            
    }

    public function setStatus($name,$status)
    {
        $model = $this->findByColumn($name,'name');
        if (is_object($model) == true) {
            return $model->update(['status' => $status]);
        }
        return false;
    }

    public function getList($type,$status = null)
    {          
        $items = $this->where('type','=',$type);  
        if ($status !== null) {
            $items = $items->where('status','=',$status);
        }
        $items = $items->get();
        return (is_object($items) == true) ? $items->toArray() : [];
    }

    public function disable($name)
    {        
        return $this->setStatus($name,Status::DISABLED());
    }

    public function enable($name)
    {
        return $this->setStatus($name,Status::ACTIVE());
    }
}
