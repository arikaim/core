<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System;

use Arikaim\Core\Utils\Curl;
use Arikaim\Core\System\Config;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\ZipFile;
use Arikaim\Core\System\ComposerApplication;

class Update 
{
    //private $source_url;
    private $core_package_name = "arikaim/core";

    public function __construct() 
    {
       // $this->source_url = "http://192.168.1.12/core.zip";  
    }

    public function update()
    {
        $errors = 0;
        return $errors;
    }

    public function getCorePackagesList()
    {
        $package_info = Self::getPackageInfo("arikaim","core");
        $list = $package_info['packages']["arikaim/core"];
        unset($list['dev-master']);
        $packages = [];

        foreach ($list as $key => $package) {          
            $info['version'] = $package['version'];
            $info['name'] = $package['name'];
            array_push($packages,$info);
        }
        return $packages;
    }

    public function getLocalPackages() 
    {
        $app = new ComposerApplication();
        $list = $app->getLocalPackages(); 

        $packages = [];
        foreach ($list as $package) {           
             $info['version'] = $package->getVersion();
             $info['name'] = $package->getName();
             array_push($packages,$info);
        }     
        return $packages;
    }

    protected function updateCore()
    {
        
    }

    public function updateUiLibrary()
    {
        
    }

    public static function getPackageInfo($vendor,$package)
    {
        $url = "https://packagist.org/p/$vendor/$package.json";
        $info = Curl::get($url);
        return json_decode($info,true);
    }
}
