<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Utils;

use Arikaim\Core\FileSystem\File;
use Arikaim\Core\System\System;

class ZipFile 
{
    public static function isInstalled()
    {
        return System::hasPhpExtension('zip');
    }

    public static function extract($zip_file,$destination_path)
    {
        if (File::exists($zip_file) == false) {
            return false;
        }

        $zip = new \ZipArchive;
        $zip->open($zip_file,\ZipArchive::OVERWRITE);
        $result = $zip->extractTo($destination_path);
        $zip->close(); 
        return $result;
    }

    public static function isValid($zip_file)
    {
    }

    public static function create($path,$zip_file)
    {
    }
}
