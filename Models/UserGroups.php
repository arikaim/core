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
use Arikaim\Core\Traits\Db\Uuid;;
use Arikaim\Core\Traits\Db\Find;;
use Arikaim\Core\Models\UserGroupMembers;

/**
 * User groups database model
 */
class UserGroups extends Model  
{
    use Uuid,
        Find;

    /**
     * Fillable attributes
     *
     * @var array
    */
    protected $fillable = [        
        'title',
        'description'
    ];

    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Group members relation
     *
     * @return UserGroupMembers
     */
    public function members()
    {
        return $this->hasMany(UserGroupMembers::class);
    }

    /**
     * Return true if user is member in current group.
     *
     * @param integer $user_id
     * @param object|null $model
     * @return boolean
     */
    public function hasUser($user_id, $model = null)
    {
        $model = (is_object($model) == false) ? $this : $model;
        $model = $model->members()->where('user_id','=',$user_id)->first();
        return is_object($model);
    }

    /**
     * Return true if user is member of gorup 
     *
     * @param integer $group_id
     * @param integer $user_id
     * @return bool
     */
    public function inGroup($group_id, $user_id)
    {
        $model = $this->where('id','=',$group_id)->get();
        return (is_object($model) == true) ? $this->hasUser($user_id,$model) : false;         
    }

    /**
     * Get user groups
     *
     * @param integer $user_id
     * @return Model
     */
    public function getUserGroups($user_id)
    {
        $model = UserGroupMembers::where('user_id','=',$user_id)->get();
        return (is_object($model) == true) ? $model : [];
    }

    /**
     * Add user to group
     *
     * @param integer $group_id
     * @param integer|string $user_id
     * @param integer|null $date_expire
     * @return bool
     */
    public function addUser($group_id, $user_id, $date_expire = null)
    {
        if ($this->findById($user_id) == true) {
            return true;
        }

        $info = [
            'group_id' => $group_id,
            'user_id'  => $user_id,
            'date_expire' => $date_expire
        ];
        $model = UserGroupMembers::create($info);
        return is_object($model);
    }

    /**
     * Remove user from group
     *
     * @param integer $group_id
     * @param integer $user_id
     * @return bool
     */
    public function removeUser($group_id, $user_id)
    {       
        $model = $this->members()->where('group_id','=',$group_id);
        $model = $model->where('user_id','=',$user_id);
        return $model->delete();
    }
}
