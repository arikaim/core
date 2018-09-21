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
use Arikaim\Core\Db\Uuid;
use Arikaim\Core\Db\Find;
use Arikaim\Core\Db\Model as DbModel;
use Arikaim\Core\Models\Users;
use Arikaim\Core\Models\UserGroups;

/**
 * Permissions database model
 */
class Permissions extends Model  
{
    use Uuid,
        Find;

    const USER  = 1;
    const GROUP = 2;
    
    protected $fillable = [
        'read',
        'write',
        'delete',
        'execute',
        'user_id',
        'name',
        'group_id'];
        
    public $timestamps = false;
    
    public function user()
    {
        return $this->belongsTo(Users::class);
    }

    public function group()
    {
        return $this->belongsTo(UserGroups::class);
    }

    public function setUserPermission($id, $name, $permissions) 
    {
        return $this->setPermission($id,$name,$permissions,Self::USER);
    }
    
    public function setGroupPermission($id, $name, $permissions)
    {
        return $this->setPermission($id,$name,$permissions,Self::GROUP);
    }

    public function getUserPermission($name, $uuid)
    {
        $permission = $this->getPermission($name,$uuid,Self::USER);
        if (is_object($permission) == true) {
            return $permission;
        }
        
        $groups = DbModel::UserGroups();
        $group_list = $groups->getUserGroups($id);
        foreach ($group_list as $group) {
            $permission = $this->getGroupPermission($name,$group['uuid']);
            if (is_object($permission) == true) {
                return $permission;
            }
        }
        return false;
    }

    public function getGroupPermission($name, $uuid)
    {
        return $this->getPermission($name,$uuid,Self::GROUP);
    }

    public function getPermission($name, $uuid, $type = Self::USER)
    {
        if (Schema::hasTable($this) == false) {          
            return false;
        }
        if ($type == Self::USER) {
            $id = DbModel::Users()->validUUID($uuid);
            $model = $this->where('user_id','=',$id);
        } else {
            $id = DbModel::UserGroups()->getId($uuid);
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
        try {
            $result = $this->create($permissions);
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
