<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Interfaces\Packages;

interface RepositoryInterface 
{  
    /**
     * Undocumented function
     *
     * @param string $name Package name
     * @param string $licese_key License key for not free packages
     * @return void
     */
    public function download($name, $licese_key);

    /**
     * Get package version
     *
     * @param string $name
     * @return string
     */
    public function getVersion($name);

    /**
     * Return access token for http request
     *
     * @return string
     */
    public function getAccessToken();

    /**
     * Login to remote repository and retrun access token
     *
     * @param string $user_name
     * @param string $password
     * @return string ( access token )
     */
    public function login($user_name, $password);
    
    /**
     * Logout
     *
     * @return void
     */
    public function logout();
}
