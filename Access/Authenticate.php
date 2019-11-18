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

use Arikaim\Core\System\Factory;
use Arikaim\Core\Access\SessionAuthProvider;
use Arikaim\Core\Interfaces\Auth\UserProviderInterface;
use Arikaim\Core\Interfaces\Auth\AuthProviderInterface;

/**
 * Manage auth.
 */
class Authenticate 
{
    // auth type id
    const AUTH_BASIC        = 1;
    const AUTH_SESSION      = 2;
    const AUTH_JWT          = 3;
    const AUTH_TOKEN        = 4;

    /**
     * Auth name
     *
     * @var array
     */
    private $authNames = ["none","basic","session","jwt",'token'];

    /**
     * Auth provider variable
     *
     * @var AuthProviderInterface
     */
    private $provider;

    /**
     * Constructor
     *
     * @param UserProviderInterface $user
     */
    public function __construct(UserProviderInterface $user = null,AuthProviderInterface $provider = null)
    {       
        $this->provider = ($provider == null) ? new SessionAuthProvider($user) : $provider;   
    }

    /**
     * Return auth provider
     *
     * @return AuthProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set auth provider
     *
     * @param AuthProviderInterface $provider
     * @return void
     */
    public function setProvider(AuthProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Change auth provider
     *
     * @param AuthProviderInterface|string $provider
     * @return Authenticate
     */
    public function withProvider($provider)
    {
        if (is_string($provider) == true) {
            $provider = $this->provider($provider);
        }

        $this->setProvider($provider);
        return $this;
    }

    /**
     * Create auth provider
     *
     * @param string $name
     * @return object|null
     */
    public function provider($name)
    {
        $id = $this->resolveAuthType($name);
        $className = $this->getAuthProviderClass($id);
        
        return Factory::createAuthProvider($className);
    }

    /**
     * Auth user 
     *
     * @param array $credentials
     * @return bool
     */
    public function authenticate(array $credentials)
    {
        return $this->provider->authenticate($credentials);
    }
    
    /**
     * Logout
     *
     * @return void
     */
    public function logout()
    {
        $this->provider->logout();
    }

    /**
     * Get logged user
     *
     * @return mixed|null
     */
    public function getUser()
    {
        return $this->provider->getUser();
    }

    /**
     * Get login attempts
     *
     * @return null|integer
     */
    public function getLoginAttempts()
    {
        return $this->provider->getLoginAttempts();
    }

    /**
     * Get auth id
     *
     * @return null|integer
     */
    public function getId()
    {
        return $this->provider->getId();
    }

    /**
     * Return true if user is logged
     *
     * @return boolean
     */
    public function isLogged()
    {
        return !empty($this->getId());
    }

    /**
     * Return auth name
     *
     * @param int $auth
     * @return string
     */
    public function getAuthName($auth)
    {
        return (isset($this->authNames[$auth]) == true) ? $this->authNames[$auth] : false;          
    }

    /**
     * Return auth type id
     *
     * @param string $name
     * @return int
     */
    public function getTypeId($name)
    {
        return array_search($name,$this->authNames);                 
    }

    /**
     * Check if auth name is valid 
     *
     * @param string $name
     * @return boolean
     */
    public function isValidAuthName($name)
    {
        return (array_search($name,$this->authNames) === false) ? false : true;     
    }

    /**
     * Resolve auth type
     *
     * @param string|integer $type
     * @return null|integer
     */
    public function resolveAuthType($type)
    {
        if (is_string($type) == true) {
            return $this->getTypeId($type);
        }
        return (is_integer($type) == true) ? $type : null;
    }

    /**
     * Create auth middleware
     *
     * @param string $auth
     * @param array $args
     * @return object|null
     */
    public function middleware($auth, $args = null)
    {
        $id = $this->resolveAuthType($auth);
        $className = $this->getAuthMiddlewareClass($id);
       
        return Factory::createMiddleware($className,$args);
    }

    /**
     * Get middleware class name
     *
     * @param integer $id
     * @return string|null
     */
    public function getAuthMiddlewareClass($id)
    {
        $classes = [
            null,
            'BasicAuthentication',
            'SessionAuthentication',
            'JwtAuthentication',
            'TokenAuthentication'
        ];
        return (isset($classes[$id]) == true) ? $classes[$id] : null;
    }

    /**
     * Get auth provider class
     *
     * @param ineteger $id
     * @return string|null
     */
    public function getAuthProviderClass($id)
    {
        $classes = [
            null,
            'BasicAuthProvider',
            'SessionAuthProvider',
            'JwtAuthProvider',
            'TokenAuthProvider'
        ];
        return (isset($classes[$id]) == true) ? $classes[$id] : null;
    }
}
