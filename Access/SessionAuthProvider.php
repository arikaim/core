<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\Access;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Interfaces\Auth\UserProviderInterface;
use Arikaim\Core\Interfaces\Auth\AuthProviderInterface;
use Arikaim\Core\Http\Session;

/**
 * Session auth provider.
 */
class SessionAuthProvider implements AuthProviderInterface
{
    /**
     * User provider
     *
     * @var UserProviderInterface
     */
    private $user;

    /**
     * Constructor
     *
     * @param UserProviderInterface $user
     */
    public function __construct(UserProviderInterface $user = null)
    {       
        $this->user = ($user == null) ? Model::Users() : $user;   
    }

    /**
     * Auth user
     *
     * @param array $credentials
     * @return bool
     */
    public function authenticate(array $credentials)
    {
        $password = (isset($credentials['password']) == true) ? $credentials['password'] : null;
        $user = $this->user->getUserByCredentials($credentials);
        $loginAttempts = $this->getLoginAttempts() + 1;

        if ($user === false) {
            Session::set('auth.login.attempts',$loginAttempts);
            return false;
        }
      
        if ($user->verifyPassword($password) == true) {
            Session::set('auth.id',$user->getAuthId());
            Session::set('auth.login.time',time());
            Session::remove('auth.login.attempts');  
            $user->updateLoginDate();
            
            return true;
        }
        Session::set('auth.login.attempts',$loginAttempts);
        
        return false;
    }
  
    /**
     * Logout
     *
     * @return void
     */
    public function logout()
    {
       Session::remove('auth.id');
       Session::remove('auth.login.time');
       Session::remove('auth.login.attempts');  
       Session::restart();
    }

    /**
     * Get logged user
     *
     * @return mixed|null
     */
    public function getUser()
    {
        $id = $this->getId();
        return (empty($id) == null) ? $this->user->findById($id) : null;        
    }

    /**
     * Gte auth id
     *
     * @return null|integer
     */
    public function getId()
    {
        return Session::get('auth.id',null);     
    }

    /**
     * Get login attempts 
     *
     * @return integer
     */
    public function getLoginAttempts()
    {
        return (integer)Session::get('auth.login.attempts',0);  
    }
}
