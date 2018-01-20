<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Extension;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Interfaces\ExtensionInterface;

class Extension implements ExtensionInterface
{
    const USER_TYPE     = 1;
    const SYSTEM_TYPE   = 2;

    public function __construct() 
    {
                
    }
    
    public function getRoutes()
    {   
    }

    public function onAfterInstall()
    {   
    }

    public function onBeforeInstall()
    {        
    }

    public function onAfterUnInstall()
    {        
    }

    public function onBeforeUnInstall()
    {        
    }
}
