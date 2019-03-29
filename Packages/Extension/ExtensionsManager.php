<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Packages\Extension;

use Arikaim\Core\System\Path;
use Arikaim\Core\System\Url;
use Arikaim\Core\Packages\PackageManager;
use Arikaim\Core\Packages\Extension\ExtensionPackage;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;

/**
 * Extensions package manager
*/
class ExtensionsManager extends PackageManager
{
    public function __construct()
    {
       parent::__construct(Path::EXTENSIONS_PATH,'extension.json');
    }

    public function createPackage($name)
    {
        $propertes = $this->loadPackageProperties($name);
        return new ExtensionPackage($propertes);
    }

    public function getPackages($cached = false, $filter = null)
    {
        $result = ($cached == true) ? Arikaim::cache()->fetch('extensions.list') : null;
        
        if (is_array($result) == false) {
            $result = $this->scan($filter);
            Arikaim::cache()->save('extensions.list',$result,5);
        } 
        return $result;
    }

    public function getInstalled($status = null, $type = ExtensionPackage::USER)
    {
        $extensions = Model::Extensions();
        if ($status != null) {
            $extensions = $extensions->where('status','=',$status);
        }
        if ($status != null) {
            $extensions = $extensions->where('type','=',$type);
        }
        $extensions = $extensions->orderBy('position')->orderBy('id');

        return $extensions->get()->keyBy('name');
    }
}
