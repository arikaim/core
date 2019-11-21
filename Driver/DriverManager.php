<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Driver;

use Arikaim\Core\Db\Model;
use Arikaim\Core\App\Factory;
use Arikaim\Core\Collection\Properties;
use Arikaim\Core\Collection\PropertiesFactory;
use Arikaim\Core\Interfaces\DriverInterface;

/**
 * Driver manager
*/
class DriverManager
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Create driver
     *
     * @param string|integer $name Driver name, id or uuid 
     * @param string|null $category
     * @return Driver|false
     */
    public function create($name, $category = null)
    {
        list($name,$category) = $this->resolveName($name,$category);
       
        $model = $this->getDriver($name,$category);
        if (is_object($model) == false) {
            return false;
        }
       
        $properties = PropertiesFactory::createFromArray($model->config);       
        $driver = Factory::createInstance($model->class); 
            
        if ($driver instanceof DriverInterface) {               
            $driver->initDriver($properties);
            return $driver->getInstance();
        } 
        return $driver;
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
      * @param string|null $extension
      * @return boolean|Model
    */
    public function install($name, $class = null, $category = null, $title = null, $description = null, $version = null, $config = [], $extension = null)
    {
        list($name,$category) = $this->resolveName($name,$category);
        $info = $this->getDriverParams($name);

        if ($info == false) {
            $version = (empty($verison) == true) ? '1.0.0' : $version;
            $info = [
                'name'           => $name,
                'category'       => $category,
                'title'          => $title,
                'class'          => $class,
                'description'    => $description,
                'version'        => $version,
                'extension_name' => $extension,
                'config'         => $config
            ];
        }

        return Model::Drivers()->add($info);
    }

    /**
     * Get driver params
     *
     * @param string|object $driver Driver obj ref or driver class
     * @return array|false
     */
    public function getDriverParams($driver)
    {
        $driver = (is_string($driver) == true && class_exists($driver) == true) ? Factory::createInstance($driver) : $driver;   
      
        if (is_subclass_of($driver,'Arikaim\\Core\\Interfaces\\DriverInterface') == true) {  
            $properties = new Properties();   
            $callback = function() use($driver,$properties) {
                $driver->createDriverConfig($properties);   
                return $properties;
            };
            $config = $callback()->toArray();     
                       
            return [
                'name'        => $driver->getDriverName(),
                'category'    => $driver->getDriverCategory(),
                'title'       => $driver->getDriverTitle(),
                'class'       => $driver->getDriverClass(),
                'description' => $driver->getDriverDescription(),
                'version'     => $driver->getDriverVersion(),
                'config'      => $config
            ];
        }
        return false;
    }

    /**
     * Uninstall driver
     *
     * @param string|integer $name Driver name, id or uuid 
     * @param string|null $category
     * @return boolean
     */
    public function unInstall($name, $category = null)
    {
        list($name,$category) = $this->resolveName($name,$category);

        return Model::Drivers()->remove($name,$category);       
    }
    
    /**
     * Return true if driver exsits
     *
     * @param string|integer $name Driver name, id or uuid 
     * @param string|null $category
     * @return object|null
     */
    public function has($name, $category = null)
    {
        list($name,$category) = $this->resolveName($name,$category);

        return Model::Drivers()->has($name,$category);
    }

    /**
     * Get driver
     *
     * @param string|integer $name Driver name, id or uuid 
     * @param string|null $category
     * @return object|false
     */
    public function getDriver($name, $category = null)
    {
        list($name,$category) = $this->resolveName($name,$category);

        return Model::Drivers()->getDriver($name,$category);
    }

    /**
     * Save driver config
     *
     * @param string|integer $name Driver name, id or uuid 
     * @param array|object $config
     * @param string|null $category
     * @return boolean
     */
    public function saveConfig($name, $config, $category = null)
    {            
        list($name,$category) = $this->resolveName($name,$category);
        $config = (is_object($config) == true) ? $config->toArray() : $config;

        $model = Model::Drivers()->getDriver($name,$category);
        if ($model !== false) {        
            $model->config = $config;
            return $model->save();        
        }

        return false;
    }

    /**
     * Get driver config
     *
     * @param string|integer $name Driver name, id or uuid $name
     * @param string|null $category
     * @return Properties
     */
    public function getConfig($name, $category = null)
    {
        list($name,$category) = $this->resolveName($name,$category);

        $model = $this->getDriver($name,$category);
        $config = (is_object($model) == true) ? $model->config : [];

        return PropertiesFactory::createFromArray($config);         
    }

    /**
     * Get drivers list
     *
     * @param string|null $category
     * @param integer $status
     * @return Model
     */
    public function getList($category = null, $status = 1)
    {
        $model = Model::Drivers();

        $model->where('status','=',$status);
        if (empty($category) == false) {
            $model = $model->where('category','=',$category);
        }

        return $model->get();
    }

    /**
     * Enable driver
     *
     * @param string $name
     * @param string|null $category
     * @return boolean
     */
    public function enable($name, $category = null)
    {
        list($name,$category) = $this->resolveName($name,$category);

        return Model::Drivers()->setStatus($name,1,$category);
    }

    /**
     * Disable driver
     *
     * @param string $name
     * @param string|null $category
     * @return boolean
     */
    public function disable($name, $category = null)
    {
        list($name,$category) = $this->resolveName($name,$category);

        return Model::Drivers()->setStatus($name,0,$category);
    }

    /**
     * Resolve driver name ( split to name and category )
     *
     * @param string $name
     * @param string|null $category
     * @return array
     */
    protected function resolveName($name, $category = null)
    {
        $tokens = explode(':',$name);
        if (isset($tokens[1]) == true) {
            return (empty($category) == true) ? $tokens : [$tokens[0],$category];          
        }
        return [$name,$category];
    }
}
