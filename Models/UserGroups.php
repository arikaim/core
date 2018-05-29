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
use Arikaim\Core\Db\UUIDAttribute;
use Arikaim\Core\Db\Model as DbModel;

/**
 * User groups database model
 */
class UserGroups extends Model  
{
    use UUIDAttribute;

    protected $fillable = [        
        'title',
        'uuid',
        'description'];
        
    public $timestamps = false;

    public function hasUser($user_id)
    {
        return $this->inGroup($this->group_id,$user_id);        
    }

    public function inGroup($groupd_id, $user_id)
    {
        $group_details = DbModel::UserGroupsDetails();
        $model = $group_details->where('group_id','=',$group_id);
        $model = $group_details->where('user_id','=',$user_id)->first();
        return (is_object($model) == false) ? true : false;  
    }

    public function getUserGroups($user_id)
    {
        $group_details = DbModel::UserGroupsDetails();
        $model = $group_details->where('user_id','=',$user_id)->get();
        return (is_object($model) == true) ? $model->toArray() : [];
    }

    public function addUser($group_id, $user_id, $date_expire = null)
    {
        $group_details = DbModel::UserGroupsDetails();
        $info['group_id'] = $group_id;
        $info['user_id'] = $user_id;
        $info['date_expire'] = $date_expire;
        $info['date_created'] = time();
        
        $result = $group_details->save($info);
        return $result;
    }

    public function removeUser($groupd_id, $user_id)
    {
        $group_details = DbModel::UserGroupsDetails();
        $model = $group_details->where('group_id','=',$group_id);
        $model = $group_details->where('user_id','=',$user_id);
        return $model->delete();
    }

    public function getId($uuid)
    {
        $model = $this->where('uuid','=',$uuid)->first();
        return (is_object($model) == true) ? $model->id : false;
    }
}
