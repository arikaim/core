<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Utils;

use Arikaim\Core\FileSystem\File;
use ZipArchive;

/**
 * Zip file helpers
 */
class ZipFile 
{
    /**
     * Extract zip arhive
     *
     * @param string $file
     * @param string $path
     * @return integer
     */
    public static function extract($file, $path)
    {
        if (File::exists($file) == false) {
            return false;
        }

        if (File::isWritable($path) == false) {
            File::setWritable($path);
        }


        $zip = new \ZipArchive;
        $result = $zip->open($file);
        if ($result !== true) {
            return false;
        }
        $result = $zip->extractTo($path);
        $zip->close(); 

        return $result;
    }

    /**
     * Check if zip arhive is valid
     *
     * @param string $file
     * @return boolean
     */
    public static function isValid($file)
    {
        $error = null;
        $zip = new \ZipArchive();

        $result = $zip->open($file, ZipArchive::CHECKCONS);
        switch($result) {
            case \ZipArchive::ER_NOZIP :
                $error = 'Not a zip archive';
                break;
            case \ZipArchive::ER_INCONS :
                $error = 'Consistency check failed';
                break;
            case \ZipArchive::ER_CRC :
                $error= 'Checksum failed';
                break;
        }      

        return ($error == null) ? true : false;
    }    
}
