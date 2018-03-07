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
use Arikaim\Core\Arikaim;

class Update 
{
    private $core_package_name = "arikaim/core";

    public function __construct() 
    {
    }

    public function update()
    {
        $errors = 0;

        if ($errors == 0) {
            // trigger event core.update
            Arikaim::event()->trigger('core.update',[]);
            return true;
        }
        return false;
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

    public static function getPackageInfo($vendor,$package)
    {
        $url = "https://packagist.org/p/$vendor/$package.json";
        $info = Curl::get($url);
        return json_decode($info,true);
    }
}
