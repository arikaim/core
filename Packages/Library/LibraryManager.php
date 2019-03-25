<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Packages\Library;

use Arikaim\Core\System\Path;
use Arikaim\Core\System\Url;
use Arikaim\Core\Packages\PackageManager;
use Arikaim\Core\Packages\Library\LibraryPackage;
use Arikaim\Core\Arikaim;

/**
 * Ui library package manager
*/
class LibraryManager extends PackageManager
{
    public function __construct()
    {
       parent::__construct(Path::LIBRARY_PATH,'library.json');
    }

    public function createPackage($name)
    {
        $propertes = $this->loadPackageProperties($name);
        return new LibraryPackage($propertes);
    }

    public function getPackages($cached = false, $filter = null)
    {
        $result = ($cached == true) ? Arikaim::cache()->fetch('library.list') : null;
        
        if (is_array($result) == false) {
            $result = $this->scan($filter);
            Arikaim::cache()->save('library.list',$result,5);
        } 
        return $result;
    }

    public function getThemeFileUrl($library_name, $theme_name)
    {
        $library = $this->createPackage($library_name);
        if (is_object($library) == false) {
            return false;
        }
        $file = $library->getThemeFile($theme_name);
        if (empty($file) == true) {
            return false;
        }
        return Url::getLibraryThemeUrl($library_name,$theme_name) . $file;
    }
}
