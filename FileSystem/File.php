<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\FileSystem;

use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\Text;
use Arikaim\Core\System\Error\Errors;

/**
 * File
*/
class File 
{
    /**
     * Load json file and return decoded array
     *
     * @param string $file_name
     * @param array $vars
     * @param boolean $to_array
     * @return array|false
     */
    public static function readJSONFile($file_name, $vars = null, $to_array = true) 
    {    
        if (File::exists($file_name) == false) {
            return false;
        }
        $json_text = Self::read($file_name);   
       
        if (is_array($vars) == true) {
            $json_text = Text::render($json_text,$vars);
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

    public static function getClassesInFile($file_name) 
    {
        if (File::exists($file_name) == false) {
            return false;
        }
        $php_code = file_get_contents($file_name);
        return Utils::getClasses($php_code);
    }

    /**
     * Check if file exists
     *
     * @param string $file_name
     * @return bool
     */
    public static function exists($file_name) 
    {
        return file_exists($file_name);           
    }

    /**
     * Return true if file is writtable
     *
     * @param string $file_name
     * @return boolean
     */
    public static function isWritable($file_name) 
    {
        return is_writable($file_name);
    }

    /**
     * Set file writtable
     *
     * @param string $file_name
     * @return boolean
     */
    public static function setWritable($file_name) 
    {
        if (Self::exists($file_name) == false) return false;
        if (Self::isWritable($file_name) == true) return true;

        chmod($file_name, 0755);
        return Self::isWritable($file_name);
    }

    /**
     * Return file size
     *
     * @param string $file_name
     * @return integer
     */
    public static function getSize($file_name)
    {
        return (File::exists($file_name) == false) ? false : filesize($file_name);          
    }

    /**
     * Get file size text.
     *
     * @param integer $size
     * @param array $labels
     * @param boolean $as_text
     * @return string|array
     */
    public static function getSizeText($size, $labels = null, $as_text = true)
    {        
        return Utils::getMemorySizeText($size,$labels,$as_text);      
    }

    /**
     * Create directory
     *
     * @param string $path
     * @param integer $mode
     * @param boolean $recursive
     * @return void
     */
    public static function makeDir($path, $mode = 0755, $recursive = true)
    {
        return (Self::exists($path) == true) ?Self::setWritable($path,$mode) : mkdir($path,$mode,$recursive);         
    }

    public static function writeUplaodedFile(array $file, $path, $mode = null, $flags = 0)
    {
        $file_name = $path . $file['name'];
        $data = explode(',',$file['data']);
        $result = Self::writeEncoded($file_name,$data[1],$flags);
        if ($result != false && $mode != null) {
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

    /**
     * Return file extension
     *
     * @param string $file_name
     * @return string
     */
    public static function getExtension($file_name)
    {
        return pathinfo($file_name, PATHINFO_EXTENSION);
    }

    /**
     * Delete file or durectiry
     *
     * @param string $file_name
     * @return bool
     */
    public static function delete($file_name)
    {
        if (Self::exists($file_name) == true) {
            return (is_dir($file_name) == true) ? Self::deleteDirectory($file_name) : unlink($file_name);          
        }
        return false;
    }

    /**
     * Return true if direcotry is empty
     *
     * @param string $path
     * @return boolean
     */
    public static function isEmpty($path)
    {
        return (count(glob("$path/*")) === 0) ? true : false;
    }
    
    /**
     * Delete directory and all sub directories
     *
     * @param string $path
     * @return bool
     */
    public static function deleteDirectory($path)
    {
        if (is_dir($path) === false) {
            return false;
        }
    
        $dir = new \RecursiveDirectoryIterator($path,\RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($dir,\RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $file) {
            if ($file->isDir() == true) {
                rmdir($file->getRealPath());
            } else {
                $result = unlink($file->getRealPath());
            }
        }
        return true;
    }

    /**
     * Read file
     *
     * @param string $file_name
     * @return mixed|null
     */
    public static function read($file_name)
    {
        return (Self::exists($file_name) == true) ? file_get_contents($file_name) : null;           
    }

    /**
     * Return true if MIME type is image
     *
     * @param string $mime_type
     * @return boolean
     */
    public static function isImageMimeType($mime_type)
    {
        return (substr($mime_type,0,5) == 'image');
    }
}
