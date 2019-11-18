<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Packages;

use Arikaim\Core\Interfaces\Packages\PackageManagerInterface;
use Arikaim\Core\Packages\PackageManager;
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
            case PackageManager::EXTENSION_PACKAGE:
                return new ExtensionsManager();
            case PackageManager::LIBRARY_PACKAGE:
                return new LibraryManager();
            case PackageManager::TEMPLATE_PACKAGE:
                return new TemplatesManager();
            case PackageManager::MODULE_PACKAGE:
                return new ModulesManager();
        }
        
        return null;
    }
}
