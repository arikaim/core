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

/**
 * Update Arikaim core
 */
class Update 
{
    private $core_package_name = "arikaim/core";

    public function __construct() 
    {
    }

    /**
     * Update
     * // TODO
     * @return boolean
     */
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

    /**
     * Return array with code packages
     *
     * @param integer $result_length Result maximum lenght
     * @return array
     */
    public function getCorePackagesList($result_length = null)
    {
        $package_info = Self::getPackageInfo("arikaim","core");
        $list = $package_info['packages']["arikaim/core"];
        unset($list['dev-master']);
        $packages = [];
        $count = 0;       
        foreach ($list as $key => $package) {          
            $info['version'] = $package['version'];
            $info['name'] = $package['name'];
            array_push($packages,$info);
            $count++;
            if (($result_length != null) && ($count >= $result_length)) {               
                return $packages;
            }
        }
        return $packages;
    }

    /**
     * Get package info
     *
     * @param string $vendor Package vendor name
     * @param string $package Package name
     * @return array
     */
    public static function getPackageInfo($vendor,$package)
    {
        $url = "https://packagist.org/p/$vendor/$package.json";
        $info = Curl::get($url);
        return json_decode($info,true);
    }
}
