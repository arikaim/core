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

use Arikaim\Core\Interfaces\ModuleInterface;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Arikaim;

/**
 * Base class for Arikaim modules.
 */
class Module implements ModuleInterface
{
    /**
     * Module config
     *
     * @var array
     */
    protected $config = [];

    /**
     * Service container item name
     *
     * @var string|null
     */
    protected $serviceName = null;
    
    /**
     * test error
     *
     * @var string|null
     */
    protected $error = null;

    /**
     * Install module
     *
     * @return bool
     */
    public function install()
    {
        return true;        
    }

    /**
      * Install driver
      *
      * @param string|object $name Driver name, full class name or driver object ref
      * @param string|null $class
      * @param string|null $category
      * @param string|null $title
      * @param string|null $description
      * @param string|null $version
      * @param array $config
      * @return boolean|Model
    */
    public function installDriver(
        $name, 
        ?string $class = null, 
        ?string $category = null,
        ?string $title = null, 
        ?string $description = null,
        ?string $version = null, 
        array $config = []
    )
    {
        return Arikaim::driver()->install($name,$class,$category,$title,$description,$version,$config);
    }

    /**
     * Boot module
     *
     * @return bool
     */
    public function boot()
    {        
        return true;
    }
    
    /**
     * Get service container item name
     *
     * @return string|null
     */
    public function getServiceName(): ?string
    {
        return $this->serviceName;
    }

    /**
     * Set service container item name
     *
     * @param string $name
     * @return void
     */
    public function setServiceName(string $name): void
    {
        $this->serviceName = $name;
    }

    /**
     * Test module function
     * 
     * @return bool
     */
    public function test()
    {        
        return true;
    }

    /**
     * Get test error
     *
     * @return string|null
     */
    public function getTestError(): ?string
    {
        return $this->error;
    }

    /**
     * Set module config
     * @param array $config
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }
    
    /**
     * Get module config
     *
     * @param string|null $key
     * @return array
     */
    public function getConfig(?string $key = null): ?array
    {
        if (empty($key) == true) {
            return $this->config;
        }

        return $this->config[$key] ?? null;
    }

    /**
     * Load module config
     *
     * @param string $name
     * @return bool
     */
    protected function loadConfig(string $name): bool
    {
        $model = Model::Modules()->findByColumn($name,'name');
        if (\is_object($model) == true) {
            $this->setConfig($model->config);
            
            return true;
        } 
        
        return false;
    }
}
