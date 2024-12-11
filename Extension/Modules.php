<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\Extension;

use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Interfaces\ModuleInterface;

/**
 * Modules service locator
 */
class Modules  
{
    /**
     * Constructor
     *
     */
    public function __construct()
    {
    } 

    /**
     * Create module instance
     *
     * @param string $name
     * @return ModuleInterface|null
     */
    public function create(string $name)
    {        
        global $arikaim;

        $module = $arikaim->get('cache')->fetch('module.' . $name);
        if ($module === false) {
            $module = Model::Modules()->getPackage($name);
            if ($module !== false) {
                $arikaim->get('cache')->save('module.' . $name,$module,5);  
            }  
        } 
        
        $obj = ($module === false) ? null : Factory::createModule($name,$module['class']);
        if (($obj instanceof ModuleInterface) == false) {
            return null;
        }

        $obj->setConfig($module['config']);
        $obj->setModuleName($name);
        $obj->boot();
        $instance = $obj->getInstance();

        return ($instance === null) ? $obj : $instance;
    }

    /**
     * Return true if module installed
     *
     * @param string $name
     * @return boolean
     */
    public function hasModule(string $name): bool
    {
        global $arikaim;

        $module = $arikaim->get('cache')->fetch('module.' . $name);
        if ($module === false) {
            $module = Model::Modules()->getPackage($name);
        }
    
        return \is_array($module);
    }

    /**
     * Load module config
     *
     * @param string $name
     * @return array|null
     */
    protected function getConfig(string $moduleName): ?array
    {
        $model = Model::Modules()->findByColumn($moduleName,'name');

        return ($model == null) ? null : $model->config;
    }
}
