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

use Arikaim\Core\Packages\Package;
use Arikaim\Core\System\Path;
use Arikaim\Core\System\Url;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Arikaim;

/**
 * Module Package class
*/
class ModulePackage extends Package
{
    const TYPE_NAME = ['service','driver','middleware','package'];

    protected $porperties_list = ['path','name','title','description','version','requires'];
    
    public function __construct($properties) 
    {
        // set default values
        $properties->type = $properties->get('type','service');
        $properties->bootable = $properties->get('bootable',false);
        $properties->service_name = $properties->get('service_name',$properties->get('name'));
        $properties->set('class',ucfirst($properties->name));

        parent::__construct($properties);
    }

    public function getClass()
    {
        return $this->properties->get('class',ucfirst($this->getName()));
    }

    public function getProperties($full = false)
    {
        if ($full == true) {
            $module = Model::Modules();
            $this->properties->set('installed',$module->isInstalled($this->getName()));
            $this->properties->set('status',$module->getStatus($this->getName()));

            $service = Factory::createModule($this->getName(),$this->getClass());
            $error = ($service == null) ? false : $service->getTestError();
            $this->properties->set('error',$error);            
        }
        return $this->properties; 
    }

    public function install()
    {
        // clear cached items
        Arikaim::cache()->deleteModuleItems();

        $data = $this->properties->toArray();
        $data['facade_class'] = $this->properties->getByPath('facade/class','');
        $data['facade_alias'] = $this->properties->getByPath('facade/alias','');
        $data['type'] = Self::getTypeId($this->properties->get('type'));
        
        unset($data['requires']);
        unset($data['help']);
        unset($data['facade']);

        $module = Model::Modules();
        $model = $module->findByColumn($this->getName(),'name');

        if (is_object($model) == true) {
            $result = $model->update($data);
        } else {
            $result = $module->create($data);
        }

        return ($result === false) ? false : true;
    }

    public function unInstall() 
    {
        // clear cached items
        Arikaim::cache()->deleteModuleItems();

        $module = Model::Modules();
        $model = $module->findByColumn($this->getName(),'name');

        return ($model == false) ? false : $model->delete();
    }

    public function enable() 
    {
        // clear cached items
        Arikaim::cache()->deleteModuleItems();

        $module = Model::Modules();
        return $module->enable($this->getName());
    }

    public function disable() 
    {
        // clear cached items
        Arikaim::cache()->deleteModuleItems();
        
        $module = Model::Modules();
        return $module->disable($this->getName());
    }   

    public static function getTypeId($type_name)
    {
        return array_search($type_name,Self::TYPE_NAME);
    }
}
