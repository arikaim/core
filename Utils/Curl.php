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

use Arikaim\Core\System\System;
use Arikaim\Core\Utils\File;

class Curl 
{   
    public function __construct() 
    {
       
    }

    public static function isInsatlled()
    {
        return System::hasPhpExtension('curl');
    }

    public static function downloadFile($url, $destination_path, $file_name)
    {
        if (Self::isInsatlled() == false) {
            //throw new \Exception("Curl no installed");
            return false;
        }

        $writable = File::setWritable($destination_path);
        if ($writable == false) {
            throw new \Exception("Destination path: $destination_path is not writable");
            return false;
        }
        echo "url:$url";
        
        $file_path = $destination_path . $file_name;
       // echo "dest:$file_path";
        $fp = fopen($file_path, 'w+');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FILE, $fp);        
        $result = curl_exec($ch);
        echo "res;$result";
        
        if ($result === false) {
            throw new \Exception("Curl error: " . curl_error($ch));
            curl_close($ch);
            fclose($fp);
            unlink($file_path);            
            return false;
        }
   
        curl_close($ch);
        fclose($fp);
        exit();
        return File::exists($file_path);
    }
}
