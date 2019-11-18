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
 * User auth interface
 */
interface UserProviderInterface
{    
    /**
     * Return unique id 
     *
     * @return mixed
     */
    public function getAuthId();

    /**
     * Get id name
     *
     * @return string
     */
    public function getAuthIdName();

    /**
     * Get user credentials
     *
     * @param array $credential
     * @return mixed
     */
    public function getUserByCredentials(array $credentials);

    /**
     * Return true if password is correct.
     *
     * @param string $password
     * @param string|null $hash
     * @return bool
     */
    public function verifyPassword($password, $hash = null);

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword();
}
