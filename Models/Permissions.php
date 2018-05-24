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
use Arikaim\Core\Db\UUIDAttribute;
use Arikaim\Core\Db\Model as DbModel;

/**
 * Permissions database model
 */
class Permissions extends Model  
{
    use UUIDAttribute;

    const USER  = 1;
    const GROUP = 2;
    
    protected $fillable = [
        'read',
        'write',
        'delete',
        'execute',
        'user_id',
        'name',
        'uuid',
        'group_id'];
        
    public $timestamps = false;
    
    public function setUserPermission($id, $name, $permissions) 
    {
        return $this->setPermission($id,$name,$permissions,Self::USER);
    }
    
    public function setGroupPermission($id, $name, $permissions)
    {
        return $this->setPermission($id,$name,$permissions,Self::GROUP);
    }

    public function getUserPermission($name, $id)
    {
        $permission = $this->getPermission($name,$id,Self::USER);
        if (is_object($permission) == true) {
            return $permission;
        }
        $groups = DbModel::UserGroups();
        $group_list = $groups->getUserGroups($id);
        foreach ($group_list as $group) {
            $permission = $this->getGroupPermission($name,$group['id']);
            if (is_object($permission) == true) {
                return $permission;
            }
        }
        return false;
    }

    public function getGroupPermission($name, $id)
    {
        return $this->getPermission($name,$id,Self::GROUP);
    }

    public function getPermission($name, $id, $type = Self::USER)
    {
        if (Schema::hasTable($this) == false) {          
            return false;
        }
        if ($type == Self::USER) {
            $model = $this->where('user_id','=',$id);
        } else {
            $model = $this->where('group_id','=',$id);
        }
        $model = $model->where('name','=',$name)->first();
        return (is_object($model) == true) ? $model : false;           
    }

    public function setPermission($id, $name, $access, $type = Self::USER) 
    {
        $permissions = $this->validatePermissions($access); 
        if ($type == Self::USER) {
            $permissions['user_id'] = $id;
        } else {
            $permissions['group_id'] = $id;
        }
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
        $count = 0;
        $count += ($this->hasRead() == false) ? 0 : 1;
        $count += ($this->hasWrite() == false) ? 0 : 1;
        $count += ($this->hasExecute() == false) ? 0 : 1;
        $count += ($this->hasExecute() == false) ? 0 : 1;
        return ($count == 4) ? true : false;
    }
}
