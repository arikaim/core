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
use Arikaim\Core\Models\Permissions;
use Arikaim\Core\Db\Uuid;
use Arikaim\Core\Db\Find;
use Arikaim\Core\Db\Status;
use Arikaim\Core\Db\DateTimeAttribute;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Access\Access;
use Arikaim\Core\Utils\DateTime;

/**
 * Users database model
*/
class Users extends Model  
{
    use Uuid,
        Find,
        Status,
        DateTimeAttribute;

    protected $fillable = [
        'user_name',
        'email',
        'password',
        'api_key',
        'api_secret',
        'access_key',
        'access_key_expire',
        'date_login'];

    public $timestamps = false;

    public function permissions()
    {
        return $this->hasMany(Permissions::class,'user_id','id');     
    }

    public function userNameExist($user_name) 
    {
        $model = $this->where("user_name","=",$user_name)->first();
        if (is_object($model) == true) {
            return true;
        }
        return false;
    }

    public function getApiUser($api_key,$api_secret)
    {
        $user = $this->where('api_key','=',$api_key);
        $user = $user->where('api_secret','=',$api_secret);
        $user = $user->where('status','=',Self::ACTIVE())->first();
        if (is_object($user) == false) {
            return false;
        }
        return $user;
    }

    public function getCurrentUser() 
    {
        return Arikaim::session()->get('user_id');        
    }

    public function getLogedUser()
    {
        if (Schema::hasTable($this) == false) {          
            return false;
        }
        $user_id = $this->getCurrentUser();  
        $model = $this->where("id","=",$user_id)->first();
        return (is_object($model) == true) ? $model : false;           
    }

    public function isLoged() 
    {
        return ($this->getCurrentUser() > 0) ? true : false;  
    }
    
    public function isLogedAdminUser() 
    {
        $uuid = Arikaim::session()->get('uuid');
        $user = $this->getControlPanelUser();
        return ($uuid == $user->uuid) ? true : false;           
    }

    public function login($user_name, $password) 
    {
        $user = $this->where('user_name','=',$user_name);
        $user = $user->orWhere('email','=',$user_name);
        $user = $user->where('status','=',Self::ACTIVE())->first();

        if (is_object($user) == false) {
            return false;
        }
        if (Self::VerifyPassword($password,$user->password) == false) {
            return false;
        }
         
        $user->date_login = time();
        $user->update();
        
        Arikaim::session()->set('user_id',$user->id);
        Arikaim::session()->set('date_login',time());
        Arikaim::session()->set('uuid',$user->uuid);
        
        return $user;
    }

    public function logout() 
    {
        Arikaim::session()->remove('user_id');
        Arikaim::session()->remove('uuid');
        Arikaim::session()->remove('login_time');
        Arikaim::access()->clearToken();
    }

    public function getControlPanelUser()
    {
        if (Schema::hasTable($this) == false) {
            return false;
        }
        $permissions = new Permissions();
        if (Schema::hasTable($permissions) == false) {
            return false;
        }
        $permissions = $permissions->where('name','=',Access::CONTROL_PANEL)->where('user_id','>',0)->first();
        if (is_object($permissions) == false) {
            return false;
        }
        $user = $this->findById($permissions->user_id);

        return (is_object($user) == false) ? false : $user;         
    }

    public function hasControlPanelUser() 
    {
        return (is_object($this->getControlPanelUser()) == false) ? false : true;
    }

    public function EncryptPassword($password, $algo = PASSWORD_BCRYPT) 
    {
        return password_hash($password,$algo);
    }

    public function isValidPassword($password)
    {
        return Self::VerifyPassword($password,$this->password);
    }

    public static function VerifyPassword($password, $hash) 
    {
        return password_verify($password,$hash);
    }

    private function getUser($user_name)
    {
        $model = $this->where('user_name','=',$user_name)->orWhere('email','=',$user_name)->first();
        return (is_object($model) == false) ? false : $model;
    }

    public function getId($user_name) 
    {
        $user = $this->getUser($user_name);   
        return (is_object($user) == true) ? $user->id : null; 
    }

    public function validUUID($uuid) 
    {
        $user = $this->where('uuid','=',$uuid)->first();
        if (is_object($user) == false) {
            return false;
        }        
        return ($user->id > 0) ? $user->id : false;
    }

    public function createUser($user_name, $password, $email = null)
    {
        $user = $this->getUser($user_name);
        if (is_object($user) == true) {
            return $user;
        }
        $info['user_name'] = $user_name;
        $info['password'] = $this->EncryptPassword($password);
        $info['email'] = $email;
        $info['api_key'] = Utils::getUUID();
        $info['api_secret'] = Utils::getRandomKey();
   
     //   try {
            return $this->create($info);
      //  } catch(\Exception $e) {
        //    return false;
       // }     
    }

    public function changePassword($id, $password)
    {       
        $model = $this->findById($id);
        if (is_object($model) == false) {
            return false;
        }
        $model->password = $this->EncryptPassword($password);    
        return $model->save();
    }    

    public function createAccessKey($uuid, $expire_period = 1800)
    {
        if ($expire_period < 1000) {
            $expire_period = 1000;
        }
        $model = $this->where('uuid','=',$uuid)->first();
        if (is_object($model) == true) {
            $model->access_key = Utils::getUUID();
            $model->access_key_expire = time() + $expire_period;
            $result = $model->save();
            return ($result == true) ? $model->access_key : false;
        }
        return false;
    }

    public function validateAccessKey($access_key)
    {
        $model = $this->where('access_key','=',$access_key)->first();
        if (is_object($model) == false) {
            // not valid access key
            return false;
        }
        if (time() > $model->access_key_expire) {
            // expired
            return false;
        }
        return true;
    }

    public function getAccessKey($id)
    {
        $model = $this->findById($id);
        if (is_object($model) == false) {
            return false;
        }
        return (time() > $model->access_key_expire) ? false : $model->access_key;          
    }
}
