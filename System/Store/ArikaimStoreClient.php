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

abstract class ArikaimStoreClient 
{

    protected $client;

    public function __construct() 
    {       
        $this->client = new ApiClient(Self::getStoreUrl());
    }

    public function getResourceList($search, $page = 1)
    {
    }

    public function downloadResource($uuid)
    {
    }

    public function getResourceVersion($uuid)
    {
    }

    public static function getStoreUrl()
    {
        return "http://store.arikaim.com/";
    }

    public abstract function getResourceListUrl(); 
    public abstract function getDownlaodResourceUrl(); 
    public abstract function getResourceVersionUrl(); 
    public abstract function getresourceDownloadTargetPath(); 
}
