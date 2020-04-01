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

use Arikaim\Core\Access\Interfaces\PermissionsInterface;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Db\Model as DbModel;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Models\Permissions;
use Arikaim\Core\Utils\Uuid as UuidFactory;

use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\PolymorphicRelations;
use Arikaim\Core\Db\Traits\Permissions as PermissionsTrait;

/**
 * Permissions database model
 */
class PermissionRelations extends Model implements PermissionsInterface
{
    const USER = 'user';
    const GROUP = 'group';

    use Uuid,
        PolymorphicRelations,
        PermissionsTrait,
        Find;
 
    /**
     * Fillable attributes
     *
     * @var array
    */
    protected $fillable = [
        'uuid',
        'read',
        'write',
        'delete',
        'execute',
        'permission_id',       
        'relation_id',
        'relation_type'               
    ];
    
    /**
     * Db table name
     *
     * @var string
     */
    protected $table = 'permission_relations';

    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;
    
     /**
     * Relation model class
     *
     * @var string
     */
    protected $relationModelClass = Permissions::class;

    /**
     * Reation column name
     *
     * @var string
     */
    protected $relationColumnName = 'permission_id';

    /**
     * Permissions model relation
     *
     * @return Permissions
     */
    public function permission()
    {
        return $this->belongsTo(Permissions::class,'permission_id');
    }

    /**
     * Get users permisssions
     *
     * @param integer $userId
     * @return mixed
     */
    public function getUserPermissions($userId)
    {
        $query = $this->getRelationsQuery($userId,'user');
      
        return $query->get();
    }

    /**
     * Get user group permisssions
     *
     * @param integer $groupId
     * @return mixed
     */
    public function getGroupPermissions($groupId)
    {
        $query = $this->getRelationsQuery($groupId,'group');
      
        return $query->get();
    }

    /**
     * Set user permission
     *
     * @param string $name
     * @param array $permissions
     * @param integer|string|null $id
     * @return Model|bool
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
     * @param string $name Permission Name Or Slug
     * @param array $permissions
     * @param integer|string $id Group Id, Uuid or Slug
     * @return Model|bool
     */
    public function setGroupPermission($name, $permissions, $id)
    {
        if (is_string($id) == true) {
            $model = DbModel::UserGroups();
            $group = $model->findById($id);
            if (is_object($group) == false) {
                $group = $model->findBySlug($id);                
            }
            if (is_object($group) == false) {
                return false;
            }

            $id = $group->id;
        }
 
        return $this->setPermission($name,$permissions,$id,Self::GROUP);
    }

    /**
     * Get user permission
     *
     * @param string $name Permission name or slug
     * @param integer|string $userId
     * @return Model|false
     */
    public function getUserPermission($name, $userId)
    {
        if (is_string($userId) == true) {
            $model = DbModel::Users()->findById($userId);
            $userId = (is_object($model) == true) ? $model->id : null; 
        }
        if (empty($userId) == true) {
            return false;
        }

        // check for user permiission
        $permission = $this->getPermission($name,$userId,Self::USER);
        if (is_object($permission) == true) {
            return $permission;
        }
        // check groups
        $groupList = DbModel::UserGroups()->getUserGroups($userId);
        foreach ($groupList as $item) {          
            $permission = $this->getGroupPermission($name,$item->group_id);
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
            if (is_object($model) == false) {
                return false;
            }
            $id = $model->id;        
        }
             
        return $this->getPermission($name,$id,Self::GROUP);
    }

    /**
     * Return permission for user or group
     *
     * @param string|integer $name Permission name or slug
     * @param integer $id
     * @param integer $type
     * @return Model|bool
     */
    public function getPermission($name, $id, $type = Self::USER)
    {
        if (Schema::hasTable($this) == false) {          
            return false;
        }
        if (empty($id) == true) {
            return false;
        }
       
        $permissionId = (is_string($name) == true) ? DbModel::Permissions()->getId($name) : $name;

        $query = $this->getRelationsQuery($id,$type);
        $query = $query->where('permission_id','=',$permissionId);
        $model = $query->first();

        return (is_object($model) == true) ? $model : false;           
    }

    /**
     * Add or Update permission 
     *
     * @param string|integer $name
     * @param array $access - ['read','write','delete','execute]
     * @param integer|null $id user Id or group Id 
     * @param integer $type
     * @return Model|bool
     */
    public function setPermission($name, $access, $id = null, $type = Self::USER) 
    {
        $permissions = $this->resolvePermissions($access); 
        $id = ($id == null && $type == Self::USER) ? Arikaim::access()->getId() : $id;
        $permissionId = DbModel::Permissions()->getId($name);     
        if (empty($permissionId) == true) {
            return false;
        }
        $model = $this->saveRelation($permissionId,$type,$id);
    
        if (is_object($model) == false) {
            $model = $this->getRelation($permissionId,$type,$id);           
        }        
        $result = $model->update($permissions);  

        return ($result === false) ? false : $model;
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
     * Add permission item.
     *
     * @param string $name    
     * @param string|null $title
     * @param string|null $description
     * @param string|null $extension
     * @return boolean
     */
    public function addPermission($name, $title = null, $description = null, $extension = null)
    {
        $model = DbModel::Permissions();

        if ($model->has($name) == true) {
            return false;
        }
        $item = [
            'uuid'           => UuidFactory::create(),
            'name'           => $name,
            'extension_name' => $extension,
            'title'          => $title,
            'description'    => $description
        ];
        $permission = $model->create($item);

        return is_object($permission);
    }    
}
