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

use Arikaim\Core\Models\Users;
use Arikaim\Core\Models\UserGroups;

use Arikaim\Core\Db\Traits\Uuid;
use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\DateCreated;

/**
 * User groups details database model
 */
class UserGroupMembers extends Model  
{
    use Uuid,
        Find,
        DateCreated;

    /**
     * Fillable attributes
     *
     * @var array
    */
    protected $fillable = [        
        'user_id',
        'group_id',
        'date_expire',
        'date_created'
    ];
    
    /**
     * Db table name
     *
     * @var string
     */
    protected $table = 'user_group_members';

    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * User group relation
     *
     * @return void
     */
    public function group()
    {
        return $this->belongsTo(UserGroups::class,'group_id');     
    }

    /**
     * User relation
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(Users::class,'user_id');     
    }

    /**
     * Add member to group
     *
     * @param string|integer $userId
     * @param string|integer $groupId
     * @return Model|false
     */
    public function addMember($userId, $groupId)
    {
        $user = new Users();
        $user = $user->findById($userId);
        if (is_object($user) == false) {
            return false;
        }

        $group = new UserGroups();
        $group = $group->findById($groupId);
        if (is_object($group) == false) {
            return false;
        }

        $member = $this->create([
            'user_id'  => $user->id,
            'group_id' => $group->id
        ]);

        return (is_object($member) == true) ? $member : false;
    }
}
