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
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model as DbModel;
use Arikaim\Core\Db\UUIDAttribute;
use Arikaim\Core\Utils\Utils;

class User extends Model  
{
    use UUIDAttribute;

    protected $fillable = ['id','user_name','email','password','uuid','api_key','api_secret'];
    public $timestamps = false;
    
    public function userNameExist($user_name) 
    {
        $model = $this->where("user_name","=",$user_name)->first();
        if (is_object($model) == true) {
            return true;
        }
        return false;
    }

    public function getCurrentUser() 
    {
        return Arikaim::session()->get('user_id');        
    }

    public function getLogedUser()
    {
        $user_id = $this->getCurrentUser();  
        $model = $this->where("id","=",$user_id)->first();
        if (is_object($model) == true) {
            return $model;
        }
        return false;
    }

    public function isLoged() 
    {
        if ($this->getCurrentUser() > 0)  return true;
        return false;
    }
    
    public function isLogedAdminUser() 
    {
        $uuid = Arikaim::session()->get('uuid');
        $admin_uuid = $this->getControlPanelUser();
        if ($uuid == $admin_uuid) {
            return true;
        }
        return false;
    }

    public function login($user_name,$password,$permissions = null) 
    {
        $user = $this->whereRaw(" (user_name = '$user_name' OR email = '$user_name') AND status = 1 ")->first();
        if (is_object($user) == false) return false;
        
        if (User::VerifyPassword($password,$user->password) == true) {
            if ($permissions != null) {

            }
            $user->last_login = time();
            $user->update();
            Arikaim::session()->set('user_id',$user->id);
            Arikaim::session()->set('login_time',time());
            Arikaim::session()->set('uuid',$user->uuid);
            return $user->uuid;
        }
        return false;
    }

    public function logout() 
    {
        Arikaim::session()->remove('user_id');
        Arikaim::session()->remove('uuid');
        Arikaim::session()->remove('login_time');
        Arikaim::cookies()->set("token",null);
    }

    public function getControlPanelUser()
    {
        $permissions = DbModel::Permissions()->getPermission(Permissions::CONTROL_PANEL);
        if (is_object($permissions) == false) {
            return false;
        }
        return $permissions->object_uuid;
    }

    public function hasControlPanelUser() 
    {
        $permissions = DbModel::Permissions()->getPermission(Permissions::CONTROL_PANEL);
        if (is_object($permissions) == false) {
            return false;
        }
        return $permissions->hasFull();
    }

    public function EncryptPassword($password,$algo = PASSWORD_BCRYPT) 
    {
        return password_hash($password,$algo);
    }

    public function isValidPassword($password)
    {
        return Self::VerifyPassword($password,$this->password);
    }

    public static function VerifyPassword($password,$hash) 
    {
        return password_verify($password,$hash);
    }

    private function getUser($user_name)
    {
        $params['user_name'] = $user_name;
        $user = $this->whereRaw(" user_name = '$user_name' OR email = '$user_name' ")->first();
        return $user;
    }

    public function getID($user_name) 
    {
        $user = $this->getUser($user_name);   
        if (is_object($user) == true) { 
            return $user->id;
        }
        return null;
    }

    public function getUUID($user_name) 
    {
        $user = $this->getUser($user_name);    
        if (is_object($user) == true) { 
            return $user->uuid;
        }
        return null;
    }

    public function validUUID($uuid) 
    {
        $user = $this->whereRaw(" uuid = '$uuid' ")->first();
        if ($user->id > 0) {
            return true;
        }
        return false;
    }

    public function createUser($user_name,$password,$email = null)
    {
        $uuid = $this->getUUID($user_name);
        if ($uuid != null) {
            return $uuid; 
        }
        $info['user_name'] = $user_name;
        $info['password'] = $this->EncryptPassword($password);
        $info['email'] = $email;
        $info['api_key'] = Utils::getUUID();
        $info['api_secret'] = Utils::getRandomKey();
        $info['created_at'] = time();

        $this->fill($info);
        try {
            $result = $this->save();
        } catch(\Exception $e) {
            return false;
        }

        if ($result == false) {
            return false;
        }
        return $this->uuid;
    }

    public function changePassword($id,$password)
    {
        if (is_numeric($id) == true) {
            $model = $this->where('id','=',$id)->first();
        } else {
            $model = $this->where('uuid','=',$id)->first();
        }

        if (is_object($model) == false) return false;
        $model->password = $this->EncryptPassword($password);    
        $result = $model->save();        
        return $result;
    }    
}
