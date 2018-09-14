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
use Arikaim\Core\Db\Uuid;
use Arikaim\Core\Db\ToggleValue;
use Arikaim\Core\Db\Position;
use Arikaim\Core\Db\Status;
use Arikaim\Core\Db\Find;


/**
 * Extensions database model
 */
class Extensions extends Model  
{
    const USER = 0;
    const SYSTEM = 1;
    const TYPE_NAME = ['user','system'];

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

    public function getTypeId($type_name)
    {
        $key = array_search($type_name,Self::TYPE_NAME);
        return $key;
    }
}
