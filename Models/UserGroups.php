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
use Arikaim\Core\Models\UserGroupMembers;

use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\Status;
use Arikaim\Core\Db\Traits\Slug;

/**
 * User groups database model
 */
class UserGroups extends Model  
{
    use Uuid,
        Slug,
        Status,
        Find;

    /**
     * Fillable attributes
     *
     * @var array
    */
    protected $fillable = [        
        'title',
        'uuid',
        'slug',
        'status',
        'description'
    ];

    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Db table name
     *
     * @var string
     */
    protected $table = 'user_groups';

    /**
     * Find group model by id, uuid, slug, title
     *
     * @param mixed $value
     * @return Model|false
     */
    public function findGroup($value)
    {
        return $this->findByColumn($value,['id','uuid','slug','title']);
    }

    /**
     * Group members relation
     *
     * @return UserGroupMembers
     */
    public function members()
    {
        return $this->hasMany(UserGroupMembers::class,'group_id');
    }

    /**
     * Return true if user is member in current group.
     *
     * @param integer $userId
     * @param object|null $model
     * @return boolean
     */
    public function hasUser($userId, $model = null)
    {
        $model = (\is_object($model) == false) ? $this : $model;
        $model = $model->members()->where('user_id','=',$userId)->first();

        return \is_object($model);
    }

    /**
     * Return true if user is member of gorup 
     *
     * @param integer|string $groupId  Group Id, Uuid or Slug
     * @param integer $userId
     * @return bool
     */
    public function inGroup($groupId, $userId)
    {
        $model = $this->findById($groupId);
        if (\is_object($model) == false) {
            $model = $this->findBySlug($groupId);
        }

        return (\is_object($model) == true) ? $this->hasUser($userId,$model) : false;         
    }

    /**
     * Get user groups
     *
     * @param integer $userId
     * @return Model
     */
    public function getUserGroups($userId)
    {
        $model = UserGroupMembers::where('user_id','=',$userId)->get();

        return (\is_object($model) == true) ? $model : [];
    }

    /**
     * Add user to group
     *
     * @param integer $groupId
     * @param integer|string $userId
     * @param integer|null $dateExpire
     * @return bool
     */
    public function addUser($groupId, $userId, $dateExpire = null)
    {
        if ($this->findById($userId) == true) {
            return true;
        }

        $info = [
            'group_id'    => $groupId,
            'user_id'     => $userId,
            'date_expire' => $dateExpire
        ];
        $model = UserGroupMembers::create($info);

        return \is_object($model);
    }

    /**
     * Remove user from group
     *
     * @param integer $groupId
     * @param integer $userId
     * @return bool
     */
    public function removeUser($groupId, $userId)
    {       
        $model = $this->members()->where('group_id','=',$groupId);
        $model = $model->where('user_id','=',$userId);
        
        return $model->delete();
    }

    /**
     * Deleet user form all groups
     *
     * @param int $userId
     * @return bool
     */
    public function deleteUser($userId)
    {
        $model = $this->members()->where('user_id','=',$userId);

        return (\is_object($model) == true) ? $model->delete() : true;
    }

    /**
     * Create group
     *
     * @param string $title
     * @param string $description
     * @return Model|false
     */
    public function createGroup($title, $description = '')
    {
        $model = $this->findByColumn($title,'title');

        if (\is_object($model) == true) {
            return false;
        }
           
        return $this->create([ 
            'title'       => $title, 
            'description' => $description            
        ]);       
    }
}
