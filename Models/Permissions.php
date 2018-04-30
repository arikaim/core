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
use Arikaim\Core\Db\Schema;

/**
 * Permissions database model
 */
class Permissions extends Model  
{
    const USER  = 'user';
    const GROUP = 'group';
    
    protected $fillable = [
        'read',
        'write',
        'delete',
        'execute',
        'object_type',
        'name',
        'object_uuid'];
        
    public $timestamps = false;
    
    public function setUserPermission($user_uuid, $name, $permissions) 
    {
        return $this->setPermission($user_uuid,$name,$permissions,Permissions::USER);
    }
    
    public function setGroupPermission($group_uuid, $name, $permissions)
    {
        return $this->setPermission($user_uuid,$name,$permissions,Permissions::GROUP);
    }

    public function getPermission($name, $object_uuid)
    {
        if (Schema::hasTable($this) == false) {          
            return false;
        }
        $model = $this->where('object_uuid','=',$object_uuid);
        $model = $model->where('name','=',$name)->first();
        
        return (is_object($model) == true) ? $model : false;           
    }

    public function setPermission($object_uuid, $name, $access, $type = Permissions::USER) 
    {
        $permissions = $this->validatePermissions($access); 
        $permissions['object_type'] = $type;
        $permissions['object_uuid'] = $object_uuid;
        $permissions['name'] = $name;
        
        $this->fill($permissions);

        try {
            $result = $this->save();
        } catch(\Exception $e) {
          return false;
        }      
        return $result;
    }

    public function validatePermissions(array $access) 
    {
        $permissions['read'] = in_array('read',$access) ? 1:0;
        $permissions['write'] = in_array('write',$access) ? 1:0;
        $permissions['delete'] = in_array('delete',$access) ? 1:0;
        $permissions['execute'] = in_array('execute',$access) ? 1:0;
        return $permissions;
    }

    public function clear()
    {
        $this->read = 0;
        $this->write = 0;
        $this->delete = 0;
        $this->execute = 0;
    }

    public function hasPermissions(array $permissions)
    {
        if ((is_array($permissions) == false) || (count($permissions) == 0)) {
            return false;
        } 
        foreach ($permissions as $permission) {            
            if ($this->hasPermission($permission) == false) {
                return false;
            }
        }
        return true;
    }

    public function hasPermission($name)
    {
        $value = $this->getAttribute($name);
        return ($value == 1) ? true : false;
    }

    public function hasRead()
    {
        return ($this->read == 1) ? true : false;
    }

    public function hasDelete()
    {
        return ($this->delete == 1) ? true : false;
    }

    public function hasExecute()
    {
        return ($this->execute == 1) ? true : false;
    }

    public function hasWrite()
    {
        return ($this->write == 1) ? true : false;
    }

    public function hasFull()
    {
        if ($this->hasRead() == false) return false;
        if ($this->hasWrite() == false) return false;
        if ($this->hasExecute() == false) return false;
        if ($this->hasExecute() == false) return false;
        return true;
    }
}
