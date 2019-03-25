<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Packages;

use Arikaim\Core\Interfaces\Packages\PackageInterface;
use Arikaim\Core\Interfaces\CollectionInterface;

/**
 * Package base class
*/
class Package implements PackageInterface
{
    protected $properties;

    public function __construct(CollectionInterface $properties) 
    {
        $this->properties = $properties;
    }

    public function getName()
    {
        return $this->properties->get('name');
    }

    public function getProperties($full = false)
    {
        return $this->properties;
    }

    public function install()   
    {        
    }

    public function unInstall() 
    {        
    }

    public function enable()    
    {
    }

    public function disable()   
    {        
    }  

    public function reInstall()
    {        
        $this->unInstall();
        return $this->install();
    }
}
