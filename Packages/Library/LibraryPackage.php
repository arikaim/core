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

use Arikaim\Core\Packages\Package;
use Arikaim\Core\System\Path;
use Arikaim\Core\System\Url;

/**
 * UI Library Package class
*/
class LibraryPackage extends Package
{

    protected $porperties_list = ['path','name','title','description','version','requires','image','files','themes','params','framework'];
    
    public function __construct($properties) 
    {
        parent::__construct($properties);
    }

    public function getProperties($full = false)
    {
        return $this->properties; 
    }

    public function install()
    {
        return true;
    }

    public function unInstall() 
    {
        return true;
    }

    public function enable() 
    {
        return true;
    }

    public function disable() 
    {
        return true;
    }   

    public function getFiles()
    {
        return $this->properties->get('files',[]);
    }

    public function getParams()
    {
        return $this->properties->get('params',[]);
    }

    public function isFramework()
    {       
        return $this->properties->get('framework',false);
    }

    public function getThemeFile($theme_name)
    {
        return $this->properties->getByPath("themes/$theme_name/file","");
    }
}
