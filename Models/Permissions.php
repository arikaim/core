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
use Illuminate\Database\Capsule\Manager;

class Permissions extends Model  
{
    const USER = 'user';
    const GROUP = 'group';
    const CONTROL_PANEL = "control_panel";
    const FULL = ['read' => 1,'write' => 1,'delete' => 1,'execute' => 1];

    protected $fillable = ['id','read','write','delete','execute','object_type','key','object_uuid'];
    public $timestamps = false;
    
    public function setUserPermission($user_uuid, $key, $permissions) 
    {
        return $this->setPermission($user_uuid,$key,Permissions::USER,$permissions);
    }
    
    public function setGroupPermission($group_uuid, $key, $permissions)
    {
        return $this->setPermission($user_uuid,$key,Permissions::GROUP,$permissions);
    }

    public function getPermission($key, $object_uuid = null)
    {
        if ($object_uuid != null) {
            $model = $this->whereRaw(" object_uuid = $object_uuid AND key = '$key' ")->first();
        } else {
            $model = $this->where("key","=",$key)->first();
        }
        if (is_object($model) == true) {
            return $model;
        }
        return null;
    }

    public function setPermission($object_uuid, $key, $type, $permissions) 
    {
        $permissions = $this->validatePermissions($permissions); 
        $permissions['object_type'] = $type;
        $permissions['object_uuid'] = $object_uuid;
        $permissions['key'] = $key;
        $this->fill($permissions);

        try {
            $result = $this->save();
        } catch(\Exception $e) {
          echo   $e->getMessage();
          return false;
        }      
        return $result;
    }

    private function validatePermissions($permissions) 
    {
        $permissions['read'] = isset($permissions['read']) ? 1:0;
        $permissions['write'] = isset($permissions['write']) ? 1:0;
        $permissions['delete'] = isset($permissions['delete']) ? 1:0;
        $permissions['execute'] = isset($permissions['execute']) ? 1:0;
        return $permissions;
    }

    public function clear()
    {
        $this->read = 0;
        $this->write = 0;
        $this->delete = 0;
        $this->execute = 0;
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
