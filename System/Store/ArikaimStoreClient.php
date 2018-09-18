<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System\Store;


use Arikaim\Core\Arikaim;
use Arikaim\Core\Api\ApiClient;

class ArikaimStoreClient 
{

    protected $client;

    public function __construct() 
    {       
        $this->client = new ApiClient(Self::getStoreUrl());
    }

    
    public static function getResourceListUrl($search, $page = 1)
    {
        return "http://store.arikaim.com/api/files/list/$search/$page";
    }

    public static function downloadResourceUrl($file_uuid, $license_key = "")
    {
        return "http://store.arikaim.com/api/file/dowload/$file_uuid/$license_key";
    }

    public static function getResourceVersionUrl($file_uuid, $license_key = "")
    {
        return "http://store.arikaim.com/api/file/version/$file_uuid/$license_key";
    }

    public static function getStoreUrl()
    {
        return "http://store.arikaim.com/";
    }
}
