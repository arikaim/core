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

use Arikaim\Core\Models\Permissions;
use Arikaim\Core\Models\UserGroupMembers;
use Arikaim\Core\Utils\DateTime;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Access\Access;
use Arikaim\Core\Interfaces\Auth\UserProviderInterface;
use Arikaim\Core\Db\Model as DbModel;

use Arikaim\Core\Traits\Db\Uuid;
use Arikaim\Core\Traits\Db\Find;
use Arikaim\Core\Traits\Db\Status;
use Arikaim\Core\Traits\Db\DateCreated;
use Arikaim\Core\Traits\Auth\Auth;
use Arikaim\Core\Traits\Auth\Password;

/**
 * Users database model
*/
class Users extends Model implements UserProviderInterface
{
    use Uuid,
        Find,
        Status,
        DateCreated,
        Auth,
        Password;     

    /**
     * Fillable attributes
     *
     * @var array
    */
    protected $fillable = [
        'user_name',
        'email',
        'email_status',
        'password',      
        'date_login',
        'date_deleted'
    ];

    /**
     * Hidden attributes
     *
     * @var array
     */
    protected $hidden = ['password'];

    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Permissions relation
     *
     * @return Permissions
     */
    public function permissions()
    {
        return $this->hasMany(Permissions::class,'user_id','id');     
    }

    /**
     * User groups relation
     *
     * @return void
     */
    public function groups()
    {
        return $this->hasMany(UserGroupMembers::class,'user_id','id');     
    }

    /**
     * Return true if user name exist
     *
     * @param string $userName
     * @return void
     */
    public function userNameExist($userName) 
    {
        $model = $this->where("user_name","=",trim($userName))->first();

        return (is_object($model) == true) ? true : false;           
    }

    /**
     * Set login date to current time
     *
     * @return boolean
     */
    public function updateLoginDate()
    {
        $this->date_login = DateTime::getCurrentTime();

        return $this->save();
    }

    /**
     * Get user by credentails
     *
     * @param array $credentials
     * @return Model|false
     */
    public function getUserByCredentials(array $credentials)
    {
        $user = $this->where('status','=',$this->ACTIVE());

        if (isset($credentials['user_name']) == true) {
            $user = $user->where('user_name','=',$credentials['user_name']);        
            if (isset($credentials['email']) == true) {
                $user = $user->orWhere('email','=',$credentials['email']);           
            }   
        }
        if (isset($credentials['email']) == true) {
            $user = $user->where('email','=',$credentials['email']);           
        }
        // by id or uuid
        if (isset($credentials['id']) == true) {
            $user = $user->where('id','=',$credentials['id']);
            $user = $user->orWhere('uuid','=',$credentials['id']);
        }
        $user = $user->first();
      
        return (is_object($user) == false) ? false : $user;
    }

    /**
     * Get user with control panel permission
     *
     * @return Model|false
     */
    public function getControlPanelUser()
    {
        if (Schema::hasTable($this) == false) {
            return false;
        }
        $permissions = DbModel::Permissions();
        if (Schema::hasTable($permissions) == false) {
            return false;
        }
        $permissionId = DbModel::PermissionsList()->getId(Access::CONTROL_PANEL);
        $permissions = $permissions->where('permission_id','=',$permissionId)->where('user_id','>',0)->first();
        if (is_object($permissions) == false) {
            return false;
        }
        $user = $this->findById($permissions->user_id);

        return (is_object($user) == false) ? false : $user;         
    }

    /**
     * Return true if user have control panel permission
     * 
     * @param integer|null $id 
     * @return boolean
     */
    public function isControlPanelUser($id = null)
    {
        $id = (empty($id) == true) ? $this->id : $id;
        $permissions = DbModel::Permissions();
        $permissionId = DbModel::PermissionsList()->getId(Access::CONTROL_PANEL);

        $permissions = $permissions->where('permission_id','=',$permissionId)->where('user_id','=',$id)->first();

        return is_object($permissions);
    }

    /**
     * Return true if admin user exist
     *
     * @return boolean
     */
    public function hasControlPanelUser() 
    {
        return (is_object($this->getControlPanelUser()) == false) ? false : true;
    }

    /**
     * Find user by user name or email
     *
     * @param string $userName
     * @return Model|false
     */
    private function getUser($userName)
    {
        $model = $this->where('user_name','=',$userName)->orWhere('email','=',$userName)->first();

        return (is_object($model) == false) ? false : $model;
    }

    /**
     * Create user
     *
     * @param string $userName
     * @param string $password
     * @param string|null $email
     * @return void
     */
    public function createUser($userName, $password, $email = null)
    {
        $user = $this->getUser($userName);
        if (is_object($user) == true) {
            return $user;
        }
        $data = [
            'user_name'  => $userName,
            'password'   => $this->EncryptPassword($password),
            'email'      => $email
        ];
   
        return $this->create($data);
    }
}
