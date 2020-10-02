<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\App;

use Arikaim\Core\Arikaim;

/**
 * Post install actions
 */
class PostInstallActions 
{
    /**
     * Run post install actions
     *
     * @return integer
     */
    public static function runPostInstallActions()
    {
        // Run post install actions on all extensions      
        $extensionManager = Arikaim::packages()->create('extension');
        $extensionManager->postInstallAllPackages();

        $actions = Arikaim::config()->loadJsonConfigFile('post-install.json');
        $errors = 0;
        foreach ($actions as $action) {           
            $result = Self::runAction($action);
            $errors += ($result == false) ? 1 : 0;
        }

        return $errors;
    }

    /**
     * Run action
     *
     * @param array $action
     * @return boolean
     */
    public static function runAction(array $action)
    {
        $command = (isset($action['command']) == true) ? $action['command'] : null;
        if (empty($command) == true) {
            return false;
        }

        $theme = (isset($action['theme']) == true) ? $action['theme'] : null;
        $extension = (isset($action['extension']) == true) ? $action['extension'] : null;
        $packageName = (empty($extension) == false) ? $extension : $theme;

        $packageType = (empty($extension) == false) ? 'extension' : 'template';

        switch($command) {
            case 'set-primary': {
                return Self::setPrimaryPackage($packageName,$packageType);
            }
        }

        return false;
    }

    /**
     * Run set primary package action
     *
     * @param string $name
     * @param string $type
     * @return boolean
     */
    public static function setPrimaryPackage($name, $type = 'extension')
    {
        $packageManager = Arikaim::packages()->create($type);
        if ($packageManager->hasPackage($name) == false) {
            return false;
        }

        $package = $packageManager->createPackage($name);
        $package->unInstall();        
        $result = $package->install(true);
        $package->setPrimary();

        return ($result === true) ? true : false;
    }
}
