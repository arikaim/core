<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Packages\Extension;

use Arikaim\Core\App\Path;
use Arikaim\Core\Packages\PackageManager;
use Arikaim\Core\Packages\Extension\ExtensionPackage;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;

/**
 * Extensions package manager
*/
class ExtensionsManager extends PackageManager
{
    /**
     * Constructor
     */
    public function __construct()
    {
       parent::__construct(Path::EXTENSIONS_PATH);
    }

    /**
     * Create extension package
     *
     * @param string $name
     * @return ExtensionPackage
     */
    public function createPackage($name)
    {      
        $propertes = $this->loadPackageProperties($name);
     
        return new ExtensionPackage($propertes,Self::EXTENSION_PACKAGE);
    }

    /**
     * Get extension packages
     *
     * @param boolean $cached
     * @param mixed $filter
     * @return array
     */
    public function getPackages($cached = false, $filter = null)
    {
        $result = ($cached == true) ? Arikaim::cache()->fetch('extensions.list') : null;
        if (is_array($result) == false) {
            $result = $this->scan($filter);
            Arikaim::cache()->save('extensions.list',$result,5);
        } 
        
        return $result;
    }

    /**
     * Get installed packages
     *
     * @param integer $status
     * @param integer $type
     * @return array
     */
    public function getInstalled($status = null, $type = null)
    {
        $extensions = Model::Extensions();
        if ($status !== null) {
            $extensions = $extensions->where('status','=',$status);
        }
      
        if ($type !== null) {           
            $extensions = $extensions->where('type','=',$type);
        }
        $extensions = $extensions->orderBy('position')->orderBy('id');

        return $extensions->get()->keyBy('name');
    }
}
