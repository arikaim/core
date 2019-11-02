<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Packages\Module;

use Arikaim\Core\Packages\Module\ModuleRepository;
use Arikaim\Core\Packages\Package;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Arikaim;
use Arikaim\Core\System\Path;
use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Interfaces\Collection\CollectionInterface;

/**
 * Module Package class
*/
class ModulePackage extends Package
{
    const SERVICE = 0;
    const PACKAGE = 1;
    const MIDDLEWARE = 2; 

    /**
     * Module type
     */
    const TYPE_NAME = ['service','package','middleware'];

    /**
     * Constructor
     *
     * @param CollectionInterface $properties
     * @param string $packageType
     */
    public function __construct(CollectionInterface $properties, $packageType) 
    {
        // set default values
        $properties->type = $properties->get('type','service');
        $properties->bootable = $properties->get('bootable',false);
        $properties->service_name = $properties->get('service_name',$properties->get('name'));
     
        parent::__construct($properties, $packageType);

        $repositoryUrl = $properties->get('repository',null);
        $this->repository = new ModuleRepository($repositoryUrl);

    }

    /**
     * Get module class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->properties->get('class',ucfirst($this->getName()));
    }

    /**
     * Get module package properties
     *
     * @param boolean $full
     * @return Collection
     */
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

     /**
     * Get module console commands class list.
     *
     * @return array
     */
    public function getConsoleCommands()
    {      
        $path = Path::getModuleConsolePath($this->getName());
        if (File::exists($path) == false) {
            return [];
        }
        $result = [];
        foreach (new \DirectoryIterator($path) as $file) {
            if (
                $file->isDot() == true || 
                $file->isDir() == true ||
                $file->getExtension() != 'php'
            ) continue;
         
            $fileName = $file->getFilename();
            $baseClass = str_replace(".php","",$fileName);
            $class = Factory::getModuleConsoleClassName($this->getName(),$baseClass);          

            $command = Factory::createInstance($class);
            if (is_subclass_of($command,'Arikaim\Core\System\Console\ConsoleCommand') == true) {                                    
                array_push($result,$class);
            }
        }     
        
        return $result;
    }

    /**
     * Install module
     *
     * @return bool
     */
    public function install()
    {
        // clear cached items
        Arikaim::cache()->deleteModuleItems();
        $data = $this->properties->toArray();

        $module = Factory::createModule($this->getName(),$this->getClass());
        if (is_object($module) == false) {
            Arikaim::errors()->addError("MODULE_CLASS_NOT_FOUND");
            return false;
        }
       
        $module->install();

        unset($data['requires']);
        unset($data['help']);
        unset($data['facade']);

        $details = [
            'facade_class'      => $this->properties->getByPath('facade/class',null),
            'facade_alias'      => $this->properties->getByPath('facade/alias',null),
            'type'              => Self::getTypeId($this->properties->get('type')),
            'category'          => $this->properties->get('category',null),
            'class'             => $this->getClass(),
            'console_commands'  => $this->getConsoleCommands()
        ];
        $data = array_merge($data,$details);
    
        $module = Model::Modules();
        $model = $module->findByColumn($this->getName(),'name');
        $result = (is_object($model) == true) ? $model->update($data) : $module->create($data);

        return !($result === false);
    }

    /**
     * Uninstall module
     *
     * @return bool
     */
    public function unInstall() 
    {
        // clear cached items
        Arikaim::cache()->deleteModuleItems();
        $model = Model::Modules()->findByColumn($this->getName(),'name');

        return (is_object($model) == false) ? false : $model->delete();
    }

    /**
     * Enable module
     *
     * @return bool
     */
    public function enable() 
    {
        // clear cached items
        Arikaim::cache()->deleteModuleItems();

        return Model::Modules()->enable($this->getName());
    }

    /**
     * Disable module
     *
     * @return bool
     */
    public function disable() 
    {
        // clear cached items
        Arikaim::cache()->deleteModuleItems();
        
        return Model::Modules()->disable($this->getName());        
    }   

    /**
     * Get type id
     *
     * @param string $typeName
     * @return integer
     */
    public static function getTypeId($typeName)
    {
        return array_search($typeName,Self::TYPE_NAME);
    }
}
