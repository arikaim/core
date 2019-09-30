<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Packages;

use Arikaim\Core\Collection\Collection;
use Arikaim\Core\Interfaces\Packages\RepositoryInterface;
use Arikaim\Core\Utils\Url;

/**
 * Package repository base class
*/
abstract class PackageRepository implements RepositoryInterface
{
    /**
     * Access token for remote package repository
     *
     * @var string
     */
    protected $access_token;

    /**
     * Download package
     *
     * @param string $name
     * @param string|null $licese_key
     * @return bool
     */
    abstract public function download($name, $licese_key = null);

    /**
     * Get package last version
     *
     * @param string $name
     * @return string|false
     */
    abstract public function getVersion($name);

    /**
     * Logoout from repository 
     *
     * @return void
     */
    public function logout()
    {
        $this->access_token = null;
    }

    /**
     * Login to remote package repository
     *
     * @param string $user_name
     * @param string $password
     * @return bool
     */
    public function login($user_name, $password)
    {
        $url = Url::REPOSITORY_URL . '/login/';
    }

    /**
     * Return access token
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    public static function unpack($file_name)
    {

    }
}