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


/**
 * Extensions database model
 */
class Extensions extends Model  
{
    use Uuid,
        Find,
        ToggleValue,
        Position,
        Status;

    protected $fillable = [
        'name',
        'title',
        'description',
        'short_description',
        'class',
        'type',
        'position',
        'version',       
        'admin_menu',
        'console_commands',
        'license_key'];
    
    public $timestamps = false;
   
    public function setAdminMenuAttribute($value)
    {
        $value = (is_array($value) == true)  ? json_encode($value) : '';         
        $this->attributes['admin_menu'] = $value;
    }

    public function setConsoleCommandsAttribute($value)
    {
        $value = (is_array($value) == true)  ? json_encode($value) : '';         
        $this->attributes['console_commands'] = $value;
    }

    public function getConsoleCommandsAttribute()
    {
        return json_decode($this->attributes['console_commands']);
    }

    public function getAdminMenuAttribute()
    {
        return json_decode($this->attributes['admin_menu']);
    }

    public function isInstalled($extension_name)
    {
        $model = $this->where('name','=',$extension_name)->first();       
        return (is_object($model) == true) ? true : false;   
    }

    public function getStatus($extension_name)
    {
        $model = $this->where('name','=',$extension_name)->first();       
        return (is_object($model) == false) ? 0 : $model->status;            
    }

    public function disable($name)
    {        
        $model = $this->findByColumn($name,'name');
        return (is_object($model) == true) ? $model->setStatus(Self::DISABLED()) : false;     
    }

    public function enable($name)
    {
        $model = $this->findByColumn($name,'name');
        return (is_object($model) == true) ? $model->setStatus(Self::ACTIVE()) : false;     
    }
}
