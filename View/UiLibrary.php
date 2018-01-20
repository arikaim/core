<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\View;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Form\Properties;

class UiLibrary 
{
    public function __construct() {
       
    }

    public function isInstalled($library_name)
    {

    }
    
    public static function getLibraryFilePath($file_name) {
        return Self::getLibraryPath() . DIRECTORY_SEPARATOR . $file_name;
    }

    public static function getLibraryPath()
    {
        return Arikaim::getViewPath() . DIRECTORY_SEPARATOR . 'library';
    }

    public function install($library_name)
    {

    }

    public function download($library_name)
    {

    }

    public function getLibraryFiles($library_name)
    {
        $properties = $this->loadProperties($library_name)->toArray();
        foreach ($properties as $key => $value) {
            # code...
        }
    }

    public function scan()
    {
        $path = Self::getLibraryPath();
        $items = [];
        foreach (new \DirectoryIterator($path) as $file) {
            if ($file->isDot() == true ) continue;
            if ($file->isDir() == true) {
                $library_name = $file->getFilename();   
                $properties = $this->loadProperties($library_name);

                $library['name'] = $library_name;
                $library['version'] = $properties->get('version','1.0');
                $library['description'] = $properties->get('description','');
                
                array_push($items,$library);
            }
        }   
        return $items;
    }

    public function loadProperties($library_name)
    {
        $file_name = Self::getLibraryPath() . DIRECTORY_SEPARATOR . $library_name . DIRECTORY_SEPARATOR . "library.json";
        $properties = new Properties($file_name,'library');            
        return $properties;
    }

    public function getFiles($library_name)
    {
        $properties = $this->loadProperties($library_name)->toArray();
        if (isset($properties['files']) == true) {
            return $properties['files'];
        }
        return [];
    }
}
