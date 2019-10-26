<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System;

use Illuminate\Database\Capsule\Manager;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Packages\Module\ModulePackage;

/**
 * Modules Middleware Loader
 */
class ModulesMiddleware 
{
    /**
     * Add modules middleware
     *   
     * @return boolean
     */
    public static function add()
    {
        $modules = Arikaim::cache()->fetch('middleware.list');
        if (is_array($modules) == false) {   
            if (Manager::schema()->hasTable('modules') == false) {
                return false;
            }            
            $modules = Model::Modules()->getList(ModulePackage::getTypeId('middleware'),1);         
            Arikaim::cache()->save('middleware.list',$modules,2);    
        }    

        foreach ($modules as $module) {             
            $instance = Factory::createModule($module['name'],$module['class']);
            if (is_object($instance) == true) {
                Arikaim::$app->add($instance);  
            }         
        }
        return true;
    }
}
