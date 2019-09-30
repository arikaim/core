<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Access;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Interfaces\Auth\UserProviderInterface;
use Arikaim\Core\Interfaces\Auth\AuthProviderInterface;

/**
 * Basic auth provider.
 */
class BasicAuthProvider implements AuthProviderInterface
{
    /**
     * User provider
     *
     * @var UserProviderInterface
     */
    private $user;

    /**
     * Auth id
     *
     * @var mixed
     */
    private $id;

    /**
     * Constructor
     *
     * @param UserProviderInterface $user
     */
    public function __construct(UserProviderInterface $user = null)
    {       
        $this->user = ($user == null) ? Model::Users() : $user;   
        $this->id = null;
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
        if ($user === false) {
            return false;
        }
      
        if ($this->user->VerifyPassword($password,$user->getPassword()) == true) {
            $this->id = $user->getAuthId();           
            return true;
        }
        return false;
    }
  
    /**
     * Logout
     *
     * @return void
     */
    public function logout()
    {
        $this->id = null;
    }

    /**
     * Get logged user
     *
     * @return mixed|null
     */
    public function getUser()
    {
        $id = $this->getId();
        return (empty($id) == false) ? $this->user->findById($id) : null;        
    }

    /**
     * Gte auth id
     *
     * @return null|integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get login attempts 
     *
     * @return integer
     */
    public function getLoginAttempts()
    {
        return null;  
    }
}
