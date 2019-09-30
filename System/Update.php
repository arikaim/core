<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System;

use Arikaim\Core\Arikaim;
use Arikaim\Core\System\System;
use Arikaim\Core\System\ComposerApplication;
use Arikaim\Core\System\Url;

/**
 * Update Arikaim core
 */
class Update 
{
    /**
     * Update Arikaim core package
     *
     * @return bool
     */
    public function update()
    {
        $errors = 0;
        $output = ComposerApplication::updatePackage(System::getCorePackageName(),true);
      
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
        return ComposerApplication::runCommand('show ' . System::getCorePackageName());
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
        $list = $package_info['package']['versions'];
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
        $info = Url::fetch("https://packagist.org/packages/$vendor/$package.json");
        return (empty($info) == true) ? null : json_decode($info,true);
    }
}
