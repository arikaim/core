<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Packages;

use Arikaim\Core\Interfaces\Packages\PackageManagerInterface;
use Arikaim\Core\Packages\Package;
use Arikaim\Core\Packages\Extension\ExtensionsManager;
use Arikaim\Core\Packages\Library\LibraryManager;
use Arikaim\Core\Packages\Template\TemplatesManager;
use Arikaim\Core\Packages\Module\ModulesManager;
use Arikaim\Core\Arikaim;

/**
 * Package managers factory class
*/
class PackageManagerFactory 
{
    /**
     * Create package manager
     *
     * @param string $packageType
     * @return PackageManagerInterface|null
     */
    public static function create($packageType)
    {
        // Control Panel only
        if (Arikaim::access()->hasControlPanelAccess() == false) {
            return null;
        }

        switch ($packageType) {
            case Package::EXTENSION:
                return new ExtensionsManager();
            case Package::LIBRARY:
                return new LibraryManager();
            case Package::TEMPLATE:
                return new TemplatesManager();
            case Package::MODULE:
                return new ModulesManager();
        }
        
        return null;
    }
}
