<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Packages;

use Arikaim\Core\Utils\Collection;
use Arikaim\Core\Interfaces\Packages\RepositoryInterface;
use Arikaim\Core\Utils\Url;

/**
 * Package repository base class
*/
abstract class PackageRepository implements RepositoryInterface
{
    protected $access_token;

    abstract public function download($name, $licese_key);
    abstract public function getVersion($name);

    public function __construct()
    {
    }

    public function setAccessToken($token)
    {
        $this->access_token = $token;
    }

    public function getAccessToken()
    {
        return $this->access_token;
    }

    public function logout()
    {
        $this->access_token = null;
    }

    public function login($user_name, $password)
    {
        $url = Url::REPOSITORY_URL . '/login/';
    }
}