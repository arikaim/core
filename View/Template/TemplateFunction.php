<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\View\Template;

use Arikaim\Core\Utils\Utils;
use Arikaim\Core\System\Path;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Db\Model;
use Arikaim\Core\View\Template\Template;
use Arikaim\Core\FileSystem\File;
use Arikaim\Core\View\Html\BaseComponent;
use Arikaim\Core\View\Html\HtmlComponent;
use Arikaim\Core\Access\Csrf;
use Arikaim\Core\Packages\Package;
use Arikaim\Core\Packages\Extension\ExtensionsManager;
use Arikaim\Core\Packages\Library\LibraryManager;
use Arikaim\Core\Packages\Template\TemplatesManager;
use Arikaim\Core\Packages\Modules\ModulesManager;

/**
 * Template functions
 */
class TemplateFunction  
{
    /**
     * Allowed classes for execute method
     *
     * @var array
     */
    private $allowed_classes;
    
    /**
     * Contain all methods not allowed for execute.
     *
     * @var array
     */
    private $deny_methods;

    /**
     * Constructor
     */
    public function __construct() 
    {
        $this->allowed_classes = Arikaim::config()->load('allowed-classes.php');
        $this->deny_methods = [];
    }

    /**
     * Container service
     *
     * @param string $service_name
     * @param string $method_name
     * @param string $params
     * @return mixed
     */
    public function service($service_name, $method_name = null, $params = null)
    {
        $service = Arikaim::$service_name();
        if ($method_name != null) {
            return $this->callMethod($service,$method_name,$params);  
        }   
        return $service;
    }

    /**
     * Create module 
     *
     * @param string $name
     * @return object|null
     */
    public function createModule($name)
    {
        return Arikaim::createModule($name);   
    }

    /**
     * Return true if extension exists
     *
     * @param string $extension_name
     * @return boolean
     */
    public function hasExtension($extension_name)
    {
        $extension = Model::Extensions()->where('name','=',$extension_name)->first();     
        return is_object($extension);          
    }

    /**
     * Run function in extension class
     *
     * @param string $extension_name
     * @param string $class_name
     * @param string $method_name
     * @param mixed $params
     * @return mixed
     */
    public function extensionMethod($extension_name, $class_name, $method_name, $params = null)
    {
        $full_class_name = Factory::getExtensionClassName($extension_name,"Classes\\$class_name");
        $this->allowClass($full_class_name);
        return $this->executeMethod($full_class_name,$method_name,$params);
    }

    /**
     * Run function
     *
     * @param string $full_class_name
     * @param string $method_name
     * @param mixed $params
     * @return mixed
     */
    public function executeMethod($full_class_name, $method_name, $params = null) 
    {
        if ($this->isAllowedClass($full_class_name) == false) {
            $vars['class_name'] = $full_class_name;
            return Arikaim::getError("NOT_ALLOWED_METHOD_ERROR",$vars);
        }
       
        $obj = Factory::createInstance($full_class_name);       
        $result = $this->callMethod($obj,$method_name,$params);    
        
        return ($result === null) ? Utils::callStatic($full_class_name,$method_name,$params) : $result;       
    }

    /**
     * Return true if function is allowed
     *
     * @param string $name
     * @return boolean
     */
    public function isAllowed($name)
    {
        return (in_array($name,$this->deny_methods) == true) ? false : true;           
    }

    /**
     * Return true if class is allowed
     *
     * @param string $class_name
     * @return boolean
     */
    public function isAllowedClass($class_name) 
    {   
        if (in_array($class_name,$this->allowed_classes) == true) {
            return true;
        }
        if (in_array(Factory::getFullClassName($class_name),$this->allowed_classes) == true) {
            return true;
        }
        return false;
    }

    /**
     * Deny function
     *
     * @param string $class_name
     * @param string $method_name
     * @return void
     */
    public function denyMethod($class_name, $method_name) 
    {
        if (is_array($this->deny_methods[$class_name]) == false) {
            $this->deny_methods[$class_name] = [];
        }
        if (in_array($method_name,$this->deny_methods[$class_name]) == false) {
            array_push($this->allowed_classes[$class_name], $method_name);
        }
    }

    /**
     * Addd class to allowed list
     *
     * @param string $class_name
     * @return void
     */
    public function allowClass($class_name) 
    {
        if (in_array($class_name,$this->allowed_classes) == false) {
            array_push($this->allowed_classes, $class_name);
        }
    }

    /**
     * Return file type
     *
     * @param string $file_name
     * @return string
     */
    public function getFileType($file_name) 
    {
        return pathinfo($file_name, PATHINFO_EXTENSION);
    }

    /**
     * Return current year
     *
     * @return string
     */
    public function currentYear()
    {
        return date("Y");
    }
    
    /**
     * Return current language
     *
     * @return array|null
     */
    public function getCurrentLanguage() 
    {
        $language = Template::getLanguage();
        $model = Model::Language()->where('code','=',$language)->first();
        return (is_object($model) == true) ? $model->toArray() : null;
    }

    /**
     * Get option
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getOption($name, $default = null) 
    {
        return Arikaim::options()->get($name,$default);          
    }

    /**
     * Get options
     *
     * @param string $search_key
     * @return array
     */
    public function getOptions($search_key)
    {
        return Arikaim::options()->searchOptions($search_key);       
    }

    /**
     * Create obj
     *
     * @param string $class_name
     * @param string|null $extension_name
     * @return object|null
     */
    public function create($class_name, $extension_name = null)
    {
        if (class_exists($class_name) == false) {
            $class_name = (empty($extension_name) == false) ? Factory::getExtensionClassName($extension_name,$class_name) : Factory::getFullClassName($class_name);
        }
     
        if ($this->isAllowedClass($class_name) == false && $extension_name == null) {
            $vars['class_name'] = $class_name;
            return Arikaim::getError("NOT_ALLOWED_METHOD_ERROR",$vars);
        }
        return Factory::createInstance($class_name);            
    }
    
    /**
     * Call method
     *
     * @param object $obj
     * @param string $method_name
     * @param mixed $params
     * @return mixed
     */
    private function callMethod($obj, $method_name, $params = null) 
    {
        return ($this->isAllowed($method_name) == false) ? null : Utils::call($obj,$method_name,$params);         
    }   

    /**
     * Load Ui library file
     *
     * @param string $library
     * @param string $file_name
     * @return string
     */
    public function loadLibraryFile($library, $file_name)
    {
        $file = Path::getLibraryFilePath($library,$file_name);
        $content = File::read($file);

        return ($content == null) ? '' : $content;
    }

    /**
     * Load component css file
     *
     * @param string $component_name
     * @return string
     */
    public function loadComponentCssFile($component_name)
    {
        $file = BaseComponent::getComponentFiles($component_name,'css');
        $content = (empty($file[0]) == false) ? File::read($file[0]['full_path'] . $file[0]['file_name']) : '';
        
        return ($content == null) ? '' : $content;
    }

    /**
     * Return csrf token field html code
     *
     * @return string
     */
    public function csrfToken()
    {
        $token = Csrf::getToken(true);          
        return '<input type="hidden" name="csrf_token" value="'. $token . '">';
    }

    /**
     * Fetch url
     *
     * @param string $url
     * @return Response|null
     */
    public function fetch($url)
    {
        $response = Arikaim::http()->get($url);
        return (is_object($response) == true) ? $response->getBody() : null;
    }

    /**
     * Exctract array as local variables in template
     *
     * @param array $context
     * @param array $data
     * @return void
     */
    public function extractArray(&$context, $data) 
    {
        if (is_array($data) == false) {
            return;
        }
        foreach($data as $key => $value) {
            $context[$key] = $value;
        }
    }  

    /**
     * Get comonent options ( control panel access is required)
     *
     * @param string $name
     * @return array|null
     */
    public function getComponentOptions($name)
    {
        return (Arikaim::access()->hasControlPanelAccess() == true) ? HtmlComponent::getOptions($name) : null;
    }

    /**
     * Create package manager
     *
     * @param string $package_type
     * @return PackageManagerInterface|null
     */
    public function packageManager($package_type)
    {
        // Control panel only
        if (Arikaim::access()->hasControlPanelAccess() == false) {
            return null;
        }

        switch ($package_type) {
            case Package::EXTENSION:
                return new ExtensionsManager();
            case Package::LIBRARY:
                return new LibraryManager();
            case Package::TEMPLATE:
                return new TemplatesManager();
            case Package::MODULE:
                return new ModulesManager();
        }
        
        return null;
    }
}
