<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\FileSystem;

use Arikaim\Core\Utils\Utils;
use Arikaim\Core\System\Errors;
use Arikaim\Core\Arikaim;

/**
 * File
*/
class File 
{
    public static function readJSONFile($file_name, $vars = null, $to_array = true) 
    {    
        if (File::exists($file_name) == false) return false;
        $json_text = Self::read($file_name);   
       
        if (is_array($vars) == true) {
            $json_text = Utils::parseProperties($json_text,$vars);
        }     
        $data = json_decode($json_text,$to_array);
        if ($data == false) {
            $error = Errors::getJsonError();
            if ($error != null) {
                $data = [];                
            }
        }
        return $data;
    }

    public static function readConfigFile($file_name) 
    {
        return File::readJSONFile(Arikaim::config()->getConfigPath() . DIRECTORY_SEPARATOR . $file_name);
    }

    public static function scanDir($path,\Closure $callback) 
    {    
        if (File::exists($path) == false) return false;   
        if (is_callable($callback) == false) return false;    

        $dir = dir($path);
        while (false !== ($file_name = $dir->read())) {
            if ($file_name == "." || $file_name == "..") continue;
            $callback($file_name,$path);
        }
        $dir->close();
        return true;
    }

    public static function getClassesInFile($file_name) 
    {
        if (File::exists($file_name) == false) {
            return false;
        }
        $php_code = file_get_contents($file_name);
        return Utils::getClasses($php_code);
    }

    public static function exists($file_name) 
    {
        return file_exists($file_name);           
    }

    public static function isWritable($file_name) 
    {
        return is_writable($file_name);
    }

    public static function setWritable($file_name) 
    {
        if (Self::exists($file_name) == false) return false;
        if (Self::isWritable($file_name) == true) return true;

        chmod($file_name, 0755);
        return Self::isWritable($file_name);
    }

    public static function getSize($file_name)
    {
        if (File::exists($file_name) == false) {
            return false;
        }
        return filesize($file_name);
    }

    public static function getSizeText($size, $labels = null, $as_text = true)
    {        
        if (is_array($labels) == false) {
            $labels = ['Bytes','KB','MB','GB','TB','PB','EB','ZB','YB'];
        }
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        $result['size'] = $size / pow(1024, $power);
        $result['label'] = labels[$power];
        return ($as_text == true) ? $result['size'] . " " . $result['label'] : $result; 
    }

    public static function makeDir($path, $mode = 0755, $recursive = true)
    {
        if (Self::exists($path) == true) {
            return Self::setWritable($path,$mode);
        }
        return mkdir($path,$mode,$recursive);
    }

    public static function getFilesPath()
    {
        return Arikaim::getRootPath() . join(DIRECTORY_SEPARATOR,array(Arikaim::getBasePath(),"arikaim","files"));
    }

    public static function getTempPath()
    {
        return Self::getFilesPath() . DIRECTORY_SEPARATOR . "temp" . DIRECTORY_SEPARATOR;
    }

    public static function downloadFile($url, $destination_path, $file_name)
    {
        // set destination path writable 
        $writable = Self::setWritable($destination_path);
        if ($writable == false) {
            // error can't make destination path writable
            return false;
        }
        $file_path = $destination_path + DIRECTORY_SEPARATOR + $file_name;
        return File::exists($file_path);
    }

    public static function writeUplaodedFile(array $file, $path, $mode = null, $flags = 0)
    {
        $file_name = $path . $file['name'];
        $data = explode(',',$file['data']);
        $result = Self::writeEncoded($file_name,$data[1],$flags);
        if (($result != false) && ($mode != null)) {
            chmod($file_name,$mode);
        }
        return $result;
    }

    public static function writeEncoded($file_name, $encoded_data, $flags = 0)
    {
        $data = base64_decode($encoded_data);
        return Self::write($file_name,$data,$flags);
    }

    public static function write($file_name, $data, $flags = 0)
    {
        return file_put_contents($file_name,$data,$flags);
    }

    public static function getExtension($file_name)
    {
        return pathinfo($file_name, PATHINFO_EXTENSION);
    }

    public static function delete($file_name)
    {
        if (Self::exists($file_name) == true) {
            $result = unlink($file_name);
            return $result;
        }
        return true;
    }

    public static function read($file_name)
    {
        if (Self::exists($file_name) == true) {
            return file_get_contents($file_name);
        }
        return null;
    }
}
