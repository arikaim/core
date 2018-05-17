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

use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Form\Properties;
use Arikaim\Core\View\Theme;

class UiLibrary 
{
    const FRAMEWORK = 1;

    protected $properties = null;

    public function __construct($library_name = null) 
    {
        if ($library_name != null) {
            $this->properties = Self::loadProperties($library_name)->toArray();
        }
    }

    public function isInstalled($library_name)
    {
    }
    
    public static function getLibraryFilePath($library_name, $file_name) {
        return Self::getLibraryPath($library_name) . DIRECTORY_SEPARATOR . $file_name;
    }

    public static function getLibraryRootPath()
    {        
        return ARIKAIM_VIEW_PATH . DIRECTORY_SEPARATOR . 'library';
    }

    public static function getLibraryPath($library_name)
    {
        return Self::getLibraryRootPath() . DIRECTORY_SEPARATOR . $library_name;
    }

    public static function getLibraryFileUrl($library_name, $file_name)
    {
        return Self::getLibraryUrl($library_name) . "/$file_name";
    }

    public static function getLibraryUrl($library_name)
    {
        return Self::getLibraryRootUrl() . "/$library_name";
    }

    public static function getLibraryRootUrl()
    {        
        return ARIKAIM_VIEW_URL . '/library';
    }

    public function install($library_name)
    {
    }

    public function download($library_name)
    {
    }

    public function scan()
    {
        $path = Self::getLibraryRootPath();
        $items = [];
        if (File::exists($path) == false) {
            return $items;
        }
        
        foreach (new \DirectoryIterator($path) as $file) {
            if ($file->isDot() == true ) continue;
            if ($file->isDir() == true) {
                $library_name = $file->getFilename();   
                $library = $this->getDetails($library_name);
                array_push($items,$library);
            }
        }   
        return $items;
    }

    public static function loadProperties($library_name)
    {
        $file_name = Self::getLibraryPath($library_name) . DIRECTORY_SEPARATOR . "library.json";
        $properties = new Properties($file_name,null,Template::getVars());            
        return $properties;
    }

    public function getDetails($library_name)
    {
        $properties = Self::loadProperties($library_name);
        $details['name'] = $library_name;
        $details['title'] = $properties->get('title','');
        $details['version'] = $properties->get('version','1.0');
        $details['description'] = $properties->get('description','');
        $details['files'] = $properties->get('files',[]);
        $details['themes'] = $properties->get('themes',[]);
        $details['params'] = $properties->get('params',[]);
        $details['framework'] = $properties->get('framework',false);
        return $details;
    }

    public function isFramework($params)
    {       
        if (isset($this->properties['framework']) == true) {           
            if ($this->properties['framework'] === true || $this->properties['framework'] == "true") {
                return true;
            }
        }
        return false;
    }
    
    public function getFiles($library_name = null)
    {
        if (empty($library_name) == false) {
            $this->properties = Self::loadProperties($library_name)->toArray();
        }
        if (isset($this->properties['files']) == true) {
            return $this->properties['files'];
        }
        return [];
    }

    public function getParams($library_name = null)
    {
        if (empty($library_name) == false) {
            $this->properties = Self::loadProperties($library_name)->toArray();
        }
        if (isset($this->properties['params']) == true) {
            return $this->properties['params'];
        }
        return [];
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public static function getThemeFile($library_name, $theme_name)
    {
        $properties = Self::loadProperties($library_name);
        return Theme::getThemeFile($properties,$theme_name);
    }

    public static function getThemeFileUrl($library_name, $theme_name)
    {
        $file = Self::getThemeFile($library_name,$theme_name);
        if (empty($file) == true) {
            return false;
        }
        $path = Theme::getLibraryThemePath($library_name,$theme_name);
        $full_file_name = $path . DIRECTORY_SEPARATOR . $file;
        if (File::exists($full_file_name) == false) {
            return false;
        }
        return Theme::getLibraryThemeUrl($library_name,$theme_name) . "$file";
    }
}
