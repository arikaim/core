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
 * File (todo  readJSONFile  )
*/
class File 
{
    /**
     * Load json file and return decoded array
     *
     * @param string $fileName
     * @param array $vars
     * @param boolean $toArray
     * @return array|false
     */
    public static function readJSONFile($fileName, $vars = null, $toArray = true) 
    {    
        if (File::exists($fileName) == false) {
            return false;
        }
        $json = Self::read($fileName);   
       
        if (is_array($vars) == true) {
            $json = Text::render($json,$vars);
        }     
        $data = json_decode($json,$toArray);
        if ($data == false) {
            $error = Errors::getJsonError();
            if ($error != null) {
                $data = [];                
            }
        }
        return $data;
    }

    public static function getClassesInFile($fileName) 
    {
        if (File::exists($fileName) == false) {
            return false;
        }
        $code = file_get_contents($fileName);
        return Utils::getClasses($code);
    }

    /**
     * Check if file exists
     *
     * @param string $fileName
     * @return bool
     */
    public static function exists($fileName) 
    {
        return file_exists($fileName);           
    }

    /**
     * Return true if file is writtable
     *
     * @param string $fileName
     * @return boolean
     */
    public static function isWritable($fileName) 
    {
        return is_writable($fileName);
    }

    /**
     * Set file writtable
     *
     * @param string $fileName
     * @return boolean
     */
    public static function setWritable($fileName) 
    {
        if (Self::exists($fileName) == false) return false;
        if (Self::isWritable($fileName) == true) return true;

        chmod($fileName, 0755);
        return Self::isWritable($fileName);
    }

    /**
     * Return file size
     *
     * @param string $fileName
     * @return integer
     */
    public static function getSize($fileName)
    {
        return (File::exists($fileName) == false) ? false : filesize($fileName);          
    }

    /**
     * Get file size text.
     *
     * @param integer $size
     * @param array $labels
     * @param boolean $asText
     * @return string|array
     */
    public static function getSizeText($size, $labels = null, $asText = true)
    {        
        return Utils::getMemorySizeText($size,$labels,$asText);      
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

    /**
     * Undocumented function
     *
     * @param array $file
     * @param string $path
     * @param integer $mode
     * @param integer $flags
     * @return boolean
     */
    public static function writeUplaodedFile(array $file, $path, $mode = null, $flags = 0)
    {
        $fileName = $path . $file['name'];
        $data = explode(',',$file['data']);
        $result = Self::writeEncoded($fileName,$data[1],$flags);
        if ($result != false && $mode != null) {
            chmod($fileName,$mode);
        }

        return $result;
    }

    /**
     * Write encoded file
     *
     * @param string $fileName
     * @param mixed $encodedData
     * @param integer $flags
     * @return boolean
     */
    public static function writeEncoded($fileName, $encodedData, $flags = 0)
    {
        $data = base64_decode($encodedData);
        return Self::write($fileName,$data,$flags);
    }

    /**
     * Write file
     *
     * @param string $fileName
     * @param mixed $data
     * @param integer $flags
     * @return boolean
     */
    public static function write($fileName, $data, $flags = 0)
    {
        return file_put_contents($fileName,$data,$flags);
    }

    /**
     * Return file extension
     *
     * @param string $fileName
     * @return string
     */
    public static function getExtension($fileName)
    {
        return pathinfo($fileName, PATHINFO_EXTENSION);
    }

    /**
     * Delete file or durectiry
     *
     * @param string $fileName
     * @return bool
     */
    public static function delete($fileName)
    {
        if (Self::exists($fileName) == true) {
            return (is_dir($fileName) == true) ? Self::deleteDirectory($fileName) : unlink($fileName);          
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
     * @param string $fileName
     * @return mixed|null
     */
    public static function read($fileName)
    {
        return (Self::exists($fileName) == true) ? file_get_contents($fileName) : null;           
    }

    /**
     * Return true if MIME type is image
     *
     * @param string $mimeType
     * @return boolean
     */
    public static function isImageMimeType($mimeType)
    {
        return (substr($mimeType,0,5) == 'image');
    }
}
