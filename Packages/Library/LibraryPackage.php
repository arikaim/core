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
use Arikaim\Core\Packages\Library\LibraryRepository;
use Arikaim\Core\Utils\Text;
use Arikaim\Core\System\Url;
use Arikaim\Core\Interfaces\Collection\CollectionInterface;

/**
 * UI Library Package class
*/
class LibraryPackage extends Package
{ 
    /**
     * Constructor
     *
     * @param CollectionInterface $properties
     */
    public function __construct(CollectionInterface $properties) 
    {
        parent::__construct($properties);
        // create repository
        $repositoryUrl = $this->properties->get('repository',null);
        $this->repository = new LibraryRepository($repositoryUrl,$this->getVersion());
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
        $params = $this->properties->get('params',[]);
        $vars = [
            'domian'    => ARIKAIM_DOMAIN,
            'base_url'  => Url::ARIKAIM_BASE_URL
        ];

        return Text::renderMultiple($params,$vars);    
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
     * @param string $theme
     * @return string
     */
    public function getThemeFile($theme)
    {
        return $this->properties->getByPath("themes/$theme/file","");
    }
}
