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

class Curl 
{   
    public function __construct() 
    {
    }

    public static function isInsatlled()
    {
        return extension_loaded('curl');
    }

    private static function create($url, $timeout = 30, $return_transfer = true)
    {
        if (Self::isInsatlled() == false) {
            return null;
        }
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,$return_transfer);
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,$timeout);
        return $curl;
    }

    private static function exec($curl)
    {
        $response = curl_exec($curl);
        curl_close($curl);
        if ($response == false) {
            $response = curl_error($curl);
        }
        return $response;
    }

    public static function request($url, $method, array $data = null, array $headers = null, $timeout = 30)
    {
        $curl = Self::create($url,$timeout);
        if (empty($curl) == true) {
            return false;
        }

        if (is_array($headers) == true) {
            curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);
        }

        curl_setopt($curl,CURLOPT_CUSTOMREQUEST,$method);
        if (is_array($data) == true) {
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
        }
        return Self::exec($curl);
    }

    public static function post($url, array $data = null, array $headers = null, $timeout = 30)
    {
        return Self::request($url,"POST",$data,$headers,$timeout);
    }

    public static function get($url, array $data = null, array $headers = null, $timeout = 30)
    {
        return Self::request($url,"GET",$data,$headers,$timeout);
    }

    public static function delete($url, array $data = null, array $headers = null, $timeout = 30)
    {
        return Self::request($url,"DELETE",$data,$headers,$timeout);
    }

    public static function put($url, array $data = null, array $headers = null, $timeout = 30)
    {
        return Self::request($url,"PUT",$data,$headers,$timeout);
    }

    public static function downloadFile($url, $destination_path, $timeout = 30)
    {
        $writable = File::setWritable($destination_path);
        if ($writable == false) {
            throw new \Exception("Destination path: $destination_path is not writable");
            return false;
        }
        $file = fopen($destination_path, 'w+');

        $curl = Self::create($url);
        curl_setopt($curl,CURLOPT_BINARYTRANSFER,true);
        curl_setopt($curl,CURLOPT_FILE,$file);     
        $result = Self::exec($curl);
        fclose($fp);

        if ($result === false) {
            unlink($destination_path);            
            return $result;
        }
        return File::exists($destination_path);
    }
}
