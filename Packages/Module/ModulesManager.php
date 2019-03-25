<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Packages\Module;

use Arikaim\Core\System\Path;
use Arikaim\Core\System\Url;
use Arikaim\Core\Packages\PackageManager;
use Arikaim\Core\Packages\Module\ModulePackage;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;

/**
 * Modules package manager
*/
class ModulesManager extends PackageManager
{
    public function __construct()
    {
       parent::__construct(Path::MODULES_PATH,'module.json');
    }

    public function createPackage($name)
    {
        $propertes = $this->loadPackageProperties($name);
        return new ModulePackage($propertes);
    }

    public function getPackages($cached = false, $filter = null)
    {
        $result = ($cached == true && $filter == null) ? Arikaim::cache()->fetch('modules.list') : null;        
        if (is_array($result) == false) {
            $result = $this->scan($filter);
            Arikaim::cache()->save('modules.list',$result,5);
        } 
        return $result;
    }
}
