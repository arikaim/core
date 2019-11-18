<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Interfaces\Auth;

/**
 * Auth provider interface
 */
interface AuthProviderInterface
{    
    /**
     * Logout
     *
     * @return void
     */
    public function logout();

    /**
     * Get current auth user
     *
     * @return mixed
     */
    public function getUser();
    
    /**
     * Get current auth id
     *
     * @return mixed
     */
    public function getId();

    /**
     * Authenticate user 
     *
     * @param array $credentials
     * @return bool
     */
    public function authenticate(array $credentials);

    /**
     * Get login attempts
     *
     * @return integer|null
     */
    public function getLoginAttempts();
}
