<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Packages\Library;

use Arikaim\Core\Packages\Package;

/**
 * UI Library Package class
*/
class LibraryPackage extends Package
{ 
    /**
     * Constructor
     *
     * @param \Arikaim\Core\Interfaces\Collection\CollectionInterface $properties
     */
    public function __construct($properties) 
    {
        parent::__construct($properties);
    }

    /**
     * Return properties
     *
     * @param boolean $full
     * @return Collection
     */
    public function getProperties($full = false)
    {
        return $this->properties; 
    }

    /**
     * Return library files
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->properties->get('files',[]);
    }

    /**
     * Get library params
     *
     * @return void
     */
    public function getParams()
    {
        return $this->properties->get('params',[]);
    }

    /**
     * Return true if library is framework
     *
     * @return boolean
     */
    public function isFramework()
    {       
        return $this->properties->get('framework',false);
    }

    /**
     * Get theme file
     *
     * @param string $theme_name
     * @return string
     */
    public function getThemeFile($theme_name)
    {
        return $this->properties->getByPath("themes/$theme_name/file","");
    }
}
