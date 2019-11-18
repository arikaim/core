<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Packages\Module;

use Arikaim\Core\System\Path;
use Arikaim\Core\Packages\PackageManager;
use Arikaim\Core\Packages\Module\ModulePackage;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;
use Arikaim\Core\System\Factory;

/**
 * Modules package manager
*/
class ModulesManager extends PackageManager
{
    /**
     * Constructor
     */
    public function __construct()
    {
       parent::__construct(Path::MODULES_PATH);
    }

    /**
     * Create module package
     *
     * @param string $name
     * @return ModulePackage
     */
    public function createPackage($name)
    {
        $propertes = $this->loadPackageProperties($name);
        
        return new ModulePackage($propertes);
    }

    /**
     * Get module packages
     *
     * @param boolean $cached
     * @param mixed $filter
     * @return array
     */
    public function getPackages($cached = false, $filter = null)
    {
        $result = ($cached == true && $filter == null) ? Arikaim::cache()->fetch('modules.list') : null;        
        if (is_array($result) == false) {
            $result = $this->scan($filter);
            Arikaim::cache()->save('modules.list',$result,5);
        } 

        return $result;
    }

    /**
     * Gte installed module packages
     *
     * @param integer $status
     * @param integer $type
     * @param string $category
     * @return array
     */
    public function getInstalled($status = null, $type = null, $category = null)
    {
        $model = Model::Modules();
        if ($status !== null) {
            $model = $model->where('status','=',$status);
        }
        if ($type !== null) {
            $model = $model->where('type','=',$type);
        }
        if ($category !== null) {
            $model = $model->where('category','=',$category);
        }
        $model = $model->orderBy('position')->orderBy('id');

        return $model->get()->keyBy('name');
    }

    /**
     * Create module instance
     *
     * @param string $name
     * @return object|null
     */
    public function create($name)
    {
        $model = Model::Modules()->findByColumn($name,'name');
        
        return (is_object($model) == false) ? null : Factory::createModule($name,$model->class);    
    }
}
