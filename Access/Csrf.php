<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Access;

use Arikaim\Core\Arikaim;

/**
 * Csrf token helpers
 */
class Csrf
{
    /**
     * Get saved token from session
     *
     * @param boolean $create
     * @return string|null
     */
    public static function getToken($create = false)
    {
        $token = Arikaim::session()->get('csrf_token',null);
        return ($create == true && empty($token) == true) ? Self::createToken() : $token;        
    }

    /**
     * Return true if token is valid
     *
     * @param string $token
     * @return bool
     */
    public static function validateToken($token)
    {
        return (empty($token) == true || Self::getToken() !== $token) ? false : true; 
    }

    /**
     * Create new token and save to session
     *
     * @return string
     */
    public static function createToken() 
    {
        $token = bin2hex(random_bytes(16));
        Arikaim::session()->set('csrf_token',$token);

        return $token;
    }
}
