<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\View\Template;

use Arikaim\Core\App\Path;
use Arikaim\Core\Arikaim;
use Arikaim\Core\App\Factory;
use Arikaim\Core\Db\Model;
use Arikaim\Core\View\Template\Template;
use Arikaim\Core\Utils\File;
use Arikaim\Core\View\Html\BaseComponent;
use Arikaim\Core\View\Html\HtmlComponent;
use Arikaim\Core\Access\Csrf;
use Arikaim\Core\System\System;

/**
 * Template functions
 */
class TemplateFunction  
{
    /**
     * Model classes requires control panel access 
     *
     * @var array
     */
    protected $protectedModels = [
        'PermissionRelations',
        'Permissions',
        'Routes',
        'Modules',
        'Events',
        'Drivers',
        'Extensions',
        'Jobs',
        'EventSubscribers'
    ];
 
    /**
     * Create model 
     *
     * @param string $modelClass
     * @param string|null $extension
     * @return Model|false
     */
    public function createModel($modelClass, $extension = null)
    {
        if (\in_array($modelClass,$this->protectedModels) == true) {
            return (Arikaim::access()->hasControlPanelAccess() == true) ? Model::create($modelClass,$extension) : false;           
        }
     
        return Model::create($modelClass,$extension);
    }

    /**
     * Container service
     *
     * @param string $serviceName
     * @return mixed
     */
    public function service($serviceName)
    {
        $service = Arikaim::$serviceName();
       
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
     * @param string $extension
     * @return boolean
     */
    public function hasExtension($extension)
    {
        $model = Model::Extensions()->where('name','=',$extension)->first();  

        return is_object($model);          
    }

    /**
     * Return file type
     *
     * @param string $fileName
     * @return string
     */
    public function getFileType($fileName) 
    {
        return pathinfo($fileName,PATHINFO_EXTENSION);
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
     * @param string $searchKey
     * @return array
     */
    public function getOptions($searchKey)
    {
        return Arikaim::options()->searchOptions($searchKey);       
    }

    /**
     * Create obj
     *
     * @param string $class
     * @param string|null $extension
     * @return object|null
     */
    public function create($class, $extension = null)
    {
        if (class_exists($class) == false) {
            $class = (empty($extension) == false) ? Factory::getExtensionClassName($extension,$class) : Factory::getFullClassName($class);
        }
     
        return Factory::createInstance($class);            
    }
    
    /**
     * Load Ui library file
     *
     * @param string $library
     * @param string $fileName
     * @return string
     */
    public function loadLibraryFile($library, $fileName)
    {
        $file = Path::getLibraryFilePath($library,$fileName);
        $content = File::read($file);

        return ($content == null) ? '' : $content;
    }

    /**
     * Load component css file
     *
     * @param string $componentName
     * @return string
     */
    public function loadComponentCssFile($componentName)
    {
        $file = BaseComponent::getComponentFiles($componentName,'css');
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
     * Get system info ( control panel access only )
     *
     * @return System
     */
    public function system()
    { 
        return (Arikaim::access()->hasControlPanelAccess() == true) ? new System() : null;
    }
}
