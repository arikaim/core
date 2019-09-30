<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Models;

use Illuminate\Database\Eloquent\Model;

use Arikaim\Core\Interfaces\Auth\PermissionsInterface;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Db\Model as DbModel;
use Arikaim\Core\Traits\Db\Uuid;
use Arikaim\Core\Traits\Db\Find;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Models\Users;
use Arikaim\Core\Models\UserGroups;
use Arikaim\Core\Models\PermissionsList;

/**
 * Permissions database model
 */
class Permissions extends Model implements PermissionsInterface
{
    use Uuid,
        Find;

    const USER  = 1;
    const GROUP = 2;
    
    /**
     * Fillable attributes
     *
     * @var array
    */
    protected $fillable = [
        'read',
        'write',
        'delete',
        'execute',
        'user_id',
        'permission_id',
        'group_id'
    ];
    
    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;
    
    /**
     * User model relation
     *
     * @return Users
     */
    public function user()
    {
        return $this->belongsTo(Users::class);
    }

    /**
     * UserGroups model relation
     *
     * @return UserGroups
     */
    public function group()
    {
        return $this->belongsTo(UserGroups::class);
    }

    /**
     * PermissionsList model relation
     *
     * @return PermissionsList
     */
    public function details()
    {
        return $this->belongsTo(PermissionsList::class,'permission_id');
    }

    /**
     * Set user permission
     *
     * @param string $name
     * @param array $permissions
     * @param integer|string|null $id
     * @return bool
     */
    public function setUserPermission($name, $permissions, $id = null) 
    {
        if (is_string($id) == true) {
            $model = DbModel::Users()->findById($id);
            $id = (is_object($model) == true) ? $model->id : null; 
        }
        return $this->setPermission($name,$permissions,$id,Self::USER);
    }
    
    /**
     * Set group permisison
     *
     * @param string $name
     * @param array $permissions
     * @param integer|string $id
     * @return bool
     */
    public function setGroupPermission($name, $permissions, $id)
    {
        if (is_string($id) == true) {
            $model = DbModel::UserGroups()->findById($id);
            $id = (is_object($model) == true) ? $model->id : null;
        }
        return $this->setPermission($name,$permissions,$id,Self::GROUP);
    }

    /**
     * Get user permission
     *
     * @param string $name
     * @param integer|null $id
     * @return Model|false
     */
    public function getUserPermission($name, $id = null)
    {
        if (is_string($id) == true) {
            $model = DbModel::Users()->findById($id);
            $id = (is_object($model) == true) ? $model->id : null; 
        }
        // check for user permiission
        $permission = $this->getPermission($name,$id,Self::USER);
        if (is_object($permission) == true) {
            return $permission;
        }

        // check groups
        $group_list = DbModel::UserGroups()->getUserGroups($id);
        foreach ($group_list as $group) {
            $permission = $this->getGroupPermission($name,$group->id);
            if (is_object($permission) == true) {
                return $permission;
            }
        }
        return false;
    }

    /**
     * Get group permission
     *
     * @param string $name
     * @param string|integer $id
     * @return Model|bool
     */
    public function getGroupPermission($name, $id)
    {
        if (is_string($id) == true) {
            $model = DbModel::UserGroups()->findById($id);
            $id = (is_object($model) == true) ? $model->id : null;
        }
      
        return (is_object($model) == true) ? $this->getPermission($name,$id,Self::GROUP) : false;      
    }

    /**
     * Return permission for user or group
     *
     * @param string|integer $name
     * @param integer|null $id
     * @param integer $type
     * @return Model|bool
     */
    public function getPermission($name, $id = null, $type = Self::USER)
    {
        if (Schema::hasTable($this) == false) {          
            return false;
        }
        $id = ($id == null && $type == Self::USER) ? Arikaim::auth()->getId() : $id;
        
        if ($type == Self::USER) { 
            $model = $this->where('user_id','=',$id);
        } else {          
            $model = $this->where('group_id','=',$id);
        }
        $permission_id = (is_string($name) == true) ? DbModel::PermissionsList()->getId($name) : $name;

        $model = $model->where('id','=',$permission_id)->first();
        return (is_object($model) == true) ? $model : false;           
    }

    /**
     * Add or Update permission 
     *
     * @param string|integer $name
     * @param array $access - ['read','write','delete','execute]
     * @param integer|null $id user Id or group Id 
     * @param integer $type
     * @return bool
     */
    public function setPermission($name, $access, $id = null, $type = Self::USER) 
    {
        $permissions = $this->resolvePermissions($access); 
        $id = ($id == null && $type == Self::USER) ? Arikaim::auth()->getId() : $id;

        if ($type == Self::USER) {
            $permissions['user_id'] = $id;
        } else {
            $permissions['group_id'] = $id;
        }
        // resolve permission name
        $permissions['permission_id'] = (is_string($name) == true) ? DbModel::PermissionsList()->getId($name) : $name;       
        if ($permissions['permission_id'] === false) {
            // not valid permission name
            return false;
        }
    
        try {
            $model = $this->getPermission($name,$id,$type);
            if (is_object($model) == true) {
                $result = $model->update($permissions);
            } else {
                $result = $this->create($permissions);
            }
            
        } catch(\Exception $e) {
            return false;
        }      
        return $result;
    }

    /**
     * Resolve permissions array
     *
     * @param array $access
     * @return array
     */
    public function resolvePermissions(array $access) 
    {
        return [
            'read'      => in_array('read',$access) ? 1:0,
            'write'     => in_array('write',$access) ? 1:0,
            'delete'    => in_array('delete',$access) ? 1:0,
            'execute'   => in_array('execute',$access) ? 1:0
        ];       
    }

    /**
     * Check for permissions in current object
     *
     * @param array $permissions
     * @param string name
     * @param mixed id
     * @return boolean
     */
    public function hasPermissions($name, $id, $permissions)
    {
        if (is_array($permissions) == false || count($permissions) == 0) {
            return false;
        } 
        $model = $this->getUserPermission($name,$id);
        if (is_object($model) == false) {
            return false;
        }
    
        foreach ($permissions as $permission) {               
            if ($model->hasPermission($permission) == false) {              
                return false;
            }
        }
     
        return true;
    }

    /**
     * Return true if have permission 
     *
     * @param string $name valid values read|write|delete|execute
     * @return boolean
     */
    public function hasPermission($name)
    {
        if (isset($this->attributes[$name]) == true) {
            return ($this->attributes[$name] == 1) ? true : false;
        }
        return false;
    }

    /**
     *Return true if have all permissions
     *
     * @return boolean
     */
    public function hasFull()
    {
        $count = 0;
        $count += ($this->hasPermission('read') == false) ? 0 : 1;
        $count += ($this->hasPermission('write') == false) ? 0 : 1;
        $count += ($this->hasPermission('delete') == false) ? 0 : 1;
        $count += ($this->hasPermission('execute') == false) ? 0 : 1;
        return ($count == 4) ? true : false;
    }
}
