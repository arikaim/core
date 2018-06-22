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
use Symfony\Component\Process\Process;
use Arikaim\Core\System\SystemProcess;

/**
 * Update Arikaim core
 */
class Update 
{
    private $core_package_name = "arikaim/core";

    public function __construct() 
    {
    }

    // TODO
    public function update()
    {
        $errors = 0;
        $output = SystemProcess::runComposerCommand('update ' . $this->core_package_name);
        
        if ($errors == 0) {
            // trigger event core.update
            Arikaim::event()->trigger('core.update',[]);
            return true;
        }
        return false;
    }

    /**
     * Return core package info
     *
     * @return string
     */
    public function getCoreInfo()
    {
        return SystemProcess::runComposerCommand('show ' . $this->core_package_name);
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
