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
use Arikaim\Core\Models\AccessTokens;

/**
 * Token auth provider.
 */
class TokenAuthProvider implements AuthProviderInterface
{
    /**
     * User provider
     *
     * @var UserProviderInterface
     */
    private $user;

     /**
     *  Token
     *
     * @var Model|null
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
        $this->token = null;
    }

    /**
     * Authenticate
     *
     * @param array $credentials
     * @return boolean
     */
    public function authenticate(array $credentials)
    {
        $token = (isset($credentials['token']) == false) ? null : $credentials['token'];
        if (empty($token) == true) {
            return false;
        }
        $model = Model::AccessTokens();

        if ($model->isExpired($token) == true) {
            return false;
        }
        $this->token = $model->getToken($token);
    
        return (is_object($model) == false) ? false : true;
    }
  
    /**
     * Logout
     *
     * @return void
     */
    public function logout()
    {   
        $this->token = null;
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
     * Get auth id
     *
     * @return null|integer
     */
    public function getId()
    {
        return (is_object($this->token) == true) ? $this->token->user_id : null;      
    }

    /**
     * Return token
     *
     * @return array
     */
    public function getToken()
    {
        return $this->token;
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

    /**
     * Create access token
     *
     * @param integer $userId
     * @param integer $type
     * @param integer $expireTime
     * @return Model|false
     */
    public function createToken($userId, $type = AccessTokens::PAGE_ACCESS_TOKEN, $expireTime = 1800)
    {
        return Model::AccessTokens()->createToken($userId,$type,$expireTime);
    }    
}
