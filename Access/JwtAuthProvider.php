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

use Arikaim\Core\Access\Jwt;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Interfaces\Auth\UserProviderInterface;
use Arikaim\Core\Interfaces\Auth\AuthProviderInterface;

/**
 * JWT auth provider.
 */
class JwtAuthProvider implements AuthProviderInterface
{
    /**
     * User provider
     *
     * @var UserProviderInterface
     */
    private $user;

     /**
     * JWT token
     *
     * @var array
     */
    private $token;

    /**
     * Constructor
     *
     * @param UserProviderInterface $user
     */
    public function __construct(UserProviderInterface $user = null)
    {       
        $this->user = ($user == null) ? Model::Users() : $user;   
        $this->clearToken();
    }

    public function authenticate(array $credentials)
    {
        $token = (isset($credentials['token']) == false) ? null : $credentials['token'];
        if (empty($token) == true) {
            return false;
        }

        if ($this->decodeToken($token) == false) {
            return false;
        }

        $id = $this->getTokenParam('user_id');
        if (empty($id) == true) {
            return false;
        }

        $user = $this->user->getUserByCredentials(['id' => $id]);
        if ($user === false) {
            return false;
        }

        return true;
    }
  
    /**
     * Logout
     *
     * @return void
     */
    public function logout()
    {
        $this->clearToken();
    }

    /**
     * Get logged user
     *
     * @return mixed|null
     */
    public function getUser()
    {
        $id = $this->getId();
        return ($id > 0) ? $this->user->findById($id) : null;        
    }

    /**
     * Gte auth id
     *
     * @return null|integer
     */
    public function getId()
    {
        return $this->getTokenParam('user_id');       
    }

    /**
     * Remove token.
     *
     * @return void
     */
    public function clearToken()
    {
        $this->token['decoded'] = null;
        $this->token['token'] = null;
    }

    /**
     * Return true if token is valid
     *
     * @return boolean
     */
    public function isValidToken()
    {
        return !empty($this->token['decoded']);           
    }

    /**
     * Create auth token.
     *
     * @param mixed $id Auth id
     * @return object
     */
    public function createToken($id) 
    {
        $jwt = new Jwt();
        $jwt->set('user_id',$id);       
        return $jwt->createToken();       
    }

    /**
     * Decode and save token data.
     *
     * @param string $tokens
     * @return boolean
     */
    public function decodeToken($token)
    {       
        $jwt = new Jwt();
        $decoded = $jwt->decodeToken($token);
        $decoded = ($decoded === false) ? null : $decoded;

        $this->token['token'] = $token;
        $this->token['decoded'] = $decoded;
       
        return !empty($decoded);
    }

    /**
     * Return token array data
     *
     * @return array
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Return token param from decoded token
     *
     * @param string $name
     * @return mixed|null
     */
    public function getTokenParam($name)
    {
        if (isset($this->token['decoded'][$name]) == false) {
            return null;
        }
        if (is_object($this->token['decoded'][$name]) == true) {            
            return $this->token['decoded'][$name]->getValue();
        }
        return null;
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
