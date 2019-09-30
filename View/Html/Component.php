<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\View\Html;

use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\Collection\Collection;
use Arikaim\Core\Filesystem\File;
use Arikaim\Core\View\Template\Template;
use Arikaim\Core\System\Url;
use Arikaim\Core\System\Path;
use Arikaim\Core\Interfaces\View\ComponentInterface;

/**
 * Html component
 */
class Component implements ComponentInterface
{
    const TEMPLATE_COMPONENT    = 1; 
    const EXTENSION_COMPONENT   = 2;
    const GLOBAL_COMPONENT      = 3; 
    const RESOLVE_LOCATION      = 4;
    
    /**
     * Component name
     *
     * @var string
     */
    protected $name;

    /**
     * Template or extension name
     *
     * @var string
     */
    protected $template_name;

    /**
     * Component path
     *
     * @var string
     */
    protected $path;

    /**
     * Type
     *
     * @var integer
     */
    protected $type;  

    /**
     * Component full path
     *
     * @var string
     */
    protected $full_path;

    /**
     * File path
     *
     * @var string
     */
    protected $file_path;

    /**
     * Language code
     *
     * @var string
     */
    protected $language;

    /**
     * Html code
     *
     * @var string
     */
    protected $html_code;

    /**
     * Component render error
     *
     * @var string
     */
    protected $error;

    /**
     * Base path
     *
     * @var string
     */
    protected $base_path;

    /**
     * UI framework name
     *
     * @var string|null
     */
    protected $framework;

    /**
     * Component files
     *
     * @var array
     */
    protected $files;

    /**
     * Options
     *
     * @var array
     */
    protected $options;

    /**
     * Optins file
     *
     * @var string
     */
    protected $options_file;

    /**
     * Properies
     *
     * @var array
     */
    protected $properties;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $base_path
     * @param string $language
     * @param string $options_file
     */
    public function __construct($name, $base_path, $language, $options_file = null) 
    {
        $this->language = $language;
        $this->options_file = $options_file;
        $this->base_path = $base_path;
        $this->error = "";
        $this->files['js'] = [];
        $this->files['css'] = [];
        $this->html_code = "";

        $this->parseName($name);
        $this->resolvePath();
        
        $this->framework = Template::getCurrentFramework();

        $this->resolvePropertiesFileName();
        $this->resolveOptionsFileName();
        $this->resolveComponentFiles();

        $this->properties = $this->loadProperties()->toArray();
        $this->options = $this->loadOptions()->toArray(); 
    }

    /**
     * Return base path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->base_path;
    }

    /**
     * Get template file
     *
     * @return string|false
     */
    public function getTemplateFile()
    {
        switch($this->type) {
            case Self::EXTENSION_COMPONENT: 
                $path = $this->template_name . DIRECTORY_SEPARATOR . 'view';
                break;
            case Self::TEMPLATE_COMPONENT: 
                $path = "";
                break;
            case Self::GLOBAL_COMPONENT: 
                $path = "";
                break;
        }  
        if (isset($this->files['html'][0]['file_name']) == true) {
            return $path . $this->file_path . $this->files['html'][0]['file_name'];
        }

        return false;
    }

    /**
     * Return true if have error
     *
     * @return boolean
     */
    public function hasError()
    {
        return (empty($this->error) == true) ? false : true;
    }

    /**
     * Return true if component have html content
     *
     * @return boolean
     */
    public function hasContent()
    {
        return ($this->getTemplateFile() == false) ? false : true;          
    }

    /**
     * Return true if component have properties
     *
     * @return boolean
     */
    public function hasProperties()
    {
        if (isset($this->files['properties']) == true) {
            return (count($this->files['properties']) > 0) ? true : false;
        }
        return false;
    }

    /**
     * Return true if component have files
     *
     * @param string $file_type
     * @return boolean
     */
    public function hasFiles($file_type = null)
    {
        if ($file_type == null) {
            return (isset($this->files[$file_type]) == true) ? true: false;
        }

        if (isset($this->files[$file_type]) == true) {
            return (count($this->files[$file_type]) > 0) ? true : false;
        }
        return false;
    }

    /**
     * Return files 
     *
     * @param string $file_type
     * @return array
     */
    public function getFiles($file_type = null)
    {
        if ($file_type == null) {
            return $this->files;
        }
        return (isset($this->files[$file_type]) == true) ? (array)$this->files[$file_type] : [];          
    }

    /**
     * Get properties
     * 
     * @param array $default
     * @return array
     */
    public function getProperties($default = [])
    {
        return (is_array($this->properties) == true) ? $this->properties : $default;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get full path
     *
     * @return string
     */
    public function getFullPath()
    {
        return $this->full_path;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get template or extension name
     *
     * @return string
     */
    public function getTemplateName()
    {
        return $this->template_name;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage() 
    {
        return $this->language;
    }

    /**
     * Get error
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Get html code
     *
     * @return string
     */
    public function getHtmlCode() 
    {
        return $this->html_code;
    }

    /**
     * Get option
     *
     * @param string $option_name
     * @param mixed $default
     * @return mixed
     */
    public function getOption($option_name, $default = null)
    {
        $option = Arrays::getValue($this->options,$option_name);
        return (empty($option) == true) ? $default : $option;          
    }

    /**
     * Set html code
     *
     * @param string $code
     * @return void
     */
    public function setHtmlCode($code) 
    {
        $this->html_code = $code;
    }

    /**
     * Set error 
     *
     * @param string $error
     * @return void
     */
    public function setError($error) 
    {
        $this->error = $error;
    }

    /**
     * Return true if component is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        $content = 0;
        $content += ($this->hasContent() == true)    ?  1 : 0;
        $content += ($this->hasFiles('js') == true)  ?  1 : 0;
        $content += ($this->hasFiles('css') == true) ?  1 : 0;
        $content += ($this->hasProperties() == true) ?  1 : 0;
        return ($content > 0) ? true : false;
    }

    /**
     * Clear content
     *
     * @return void
     */
    public function clearContent()
    {
        $this->files['js'] = [];
        $this->files['css'] = [];
        $this->files['html'] = [];
    }

    /**
     * Add files
     *
     * @param string $files
     * @param string $file_type
     * @return bool
     */
    public function addFiles($files, $file_type)
    {
        if (is_array($files) == false) {
            return false;
        }
        if (isset($this->files[$file_type]) == false) {
            $this->files[$file_type] = [];
        }
        foreach ($files as $file) {
            if (empty($file) == false) {
                array_push($this->files[$file_type],$file);     
            }                  
        }
        return true;            
    }

    /**
     * Add component file
     *
     * @param string $file_ext
     * @return void
     */
    public function addComponentFile($file_ext)
    {
        $file_name = $this->getComponentFile($file_ext);
        if ($file_name === false) {
            return false;
        }
        $file = [
            'file_name' => $file_name,
            'path'      => $this->file_path,
            'full_path' => $this->getFullPath(),
            'url'       => $this->getFileUrl($file_name) 
        ];
        return $this->addFile($file,$file_ext);       
    }

    /**
     * Add file
     *
     * @param string $file
     * @param string $file_type
     * @return void
     */
    public function addFile($file, $file_type)
    {
        if (is_array($file) == false) {
            return false;
        }

        if (isset($this->files[$file_type]) == false) {
            $this->files[$file_type] = [];
        }
        array_push($this->files[$file_type],$file);
    }

    /**
     * Parse component name 
     *  [extesnon name | template name]:[name path]
     *  for current template  [name path]
     *  [extenstion name] :: [name path]
     * 
     * @param string $name
     * @return void
     */
    protected function parseName($name)
    {
        if (stripos($name,'::') !== false) {
            // extension component
            $tokens = explode('::',$name);     
            $type = Self::EXTENSION_COMPONENT;
        } elseif (stripos($name,'>') !== false) {
            // resolve location
            $tokens = explode('>',$name);
            $type = Self::RESOLVE_LOCATION;

        } else {
            // template component
            $tokens = explode(':',$name);  
            $type = ($tokens[0] == 'components') ? Self::GLOBAL_COMPONENT : Self::TEMPLATE_COMPONENT;    
        }

        if (isset($tokens[1]) == false) {    
            // component location not set                     
            $this->path = str_replace('.','/',$tokens[0]);            
            $this->template_name = Template::getTemplateName(); 
            $type = Self::TEMPLATE_COMPONENT;        
        } else {
            $this->path = str_replace('.','/',$tokens[1]);
            $this->template_name = $tokens[0];          
        }

        if ($type == Self::RESOLVE_LOCATION) {
            $type = (File::exists($this->getComponentFullPath(Self::TEMPLATE_COMPONENT)) == true) ? Self::TEMPLATE_COMPONENT : Self::EXTENSION_COMPONENT;
        }

        $this->type = $type;
        $parts = explode('/',$this->path);
        $this->name = end($parts);
    }   

    /**
     * Get properties file name
     *
     * @return string|false
     */
    public function getPropertiesFileName() 
    {
        if (isset($this->files['properties']['file_name']) == true) {
            return $this->files['properties']['file_name'];
        }
        return false;
    }

    /**
     * Set properties file name
     *
     * @param string $file_name
     * @return void
     */
    public function setPropertiesFileName($file_name) 
    { 
        $this->files['properties']['file_name'] = $file_name;          
    }

    /**
     * Get options file name
     *
     * @return string|false
     */
    public function getOptionsFileName()
    {
        if (isset($this->files['options']['file_name']) == true) {
            return $this->files['options']['file_name'];
        }
        return false;
    }

    /**
     * Set options file name
     *
     * @param string $file_name
     * @return void
     */
    public function setOptionsFileName($file_name)
    {
        $this->files['options']['file_name'] = $file_name;
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray()
    {
        return (array)$this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        switch ($this->type) {
            case Self::TEMPLATE_COMPONENT:
                $url = Url::getTemplateUrl($this->template_name);
                break;
            case Self::EXTENSION_COMPONENT:
                $url = Url::getExtensionViewUrl($this->template_name);
                break;   
            case Self::GLOBAL_COMPONENT:
                $url = Url::ARIKAIM_VIEW_URL;
                break;                    
        }
        return $url . "/" . $this->base_path . "/" . $this->path . "/";
    }

    /**
     * Return root component name
     *
     * @return string
     */
    public function getRootComponentPath()
    {
        return Path::getTemplatePath($this->template_name,$this->type);
    }

    /**
     * Get UI framework path
     *
     * @return string
     */
    public function getFrameworkPath()
    {
        return (empty($this->framework) == false) ? "." . $this->framework . DIRECTORY_SEPARATOR : "";          
    }

    /**
     * Get component html file
     *
     * @param string $file_ext
     * @param string $language_code
     * @return string|false
     */
    public function getComponentFile($file_ext = "html", $language_code = "") 
    {         
        $file_name = $this->getName() . $language_code . "." . $file_ext;
        // try framework path
        $full_file_name = $this->getFullPath() . $this->getFrameworkPath() . $file_name;
        if (File::exists($full_file_name) == true) {
            return $this->getFrameworkPath() . $file_name;
        }
        // try default path 
        $full_file_name = $this->getFullPath() . DIRECTORY_SEPARATOR . $file_name;
        return File::exists($full_file_name) ? $file_name : false;
    }

    /**
     * Convert file path to url
     *
     * @param string $file_name
     * @return string
     */
    public function getFileUrl($file_name)
    {
        return $this->getUrl() . $file_name;
    }

    /**
     * Load properties json file
     *
     * @return Collection
     */
    public function loadProperties()
    {       
        return Collection::createFromFile($this->getPropertiesFileName());                       
    }

    /**
     * Load options json file
     *
     * @return Collection
     */
    public function loadOptions()
    {       
        return Collection::createFromFile($this->getOptionsFileName());               
    }

    public function getComponentFullPath($type)
    {
        $template_full_path = Path::getTemplatePath($this->template_name,$type); 
        $base_path = (empty($this->base_path) == false) ? $this->base_path : '';
        $path = $base_path . DIRECTORY_SEPARATOR . $this->path . DIRECTORY_SEPARATOR;   
        
        return $template_full_path . $path;     
    }

    /**
     * Resolve component path
     *
     * @return void
     */
    protected function resolvePath() 
    {                 
        $base_path = (empty($this->base_path) == false) ? DIRECTORY_SEPARATOR . $this->base_path : '';
        $path = $base_path . DIRECTORY_SEPARATOR . $this->path . DIRECTORY_SEPARATOR;   
      
        switch($this->type) {
            case Self::EXTENSION_COMPONENT:
                $template_path = '';
                break;
            case Self::TEMPLATE_COMPONENT:
                $template_path = $this->template_name . DIRECTORY_SEPARATOR;
                break;
            case Self::GLOBAL_COMPONENT:
                $template_path = '';               
                $path = $this->path . DIRECTORY_SEPARATOR; 
                break;
            default:
                $template_path = "";      
        }
        $this->full_path = $this->getComponentFullPath($this->type);
        $this->file_path = $template_path  . $path;  
    }

    /**
     * Resolve properties file name
     *
     * @return void
     */
    private function resolvePropertiesFileName()
    {
        $language_code = ($this->language != "en") ? "-". $this->language : "";
        $file_name = $this->getComponentFile("json",$language_code);

        if ($file_name === false) {
            $file_name = $this->getComponentFile("json");
            if ($file_name === false) {
                return false;
            }
        } 
        $this->setPropertiesFileName($this->getFullPath() . $file_name);   
    }

    /**
     * Resolve options file name
     *
     * @param string|null $parent_path
     * @return bool
     */
    private function resolveOptionsFileName($path = null, $iterations = 0)
    {   
        if (empty($path) == true) {
            $path = $this->getFullPath();
        } 
      
        $file_name = $path . $this->options_file;
        if (File::exists($file_name) == false) {
            $parent_path = Path::getParentPath($path) . DIRECTORY_SEPARATOR;  
    
            if (empty($parent_path) == false && $iterations == 0) {
                return $this->resolveOptionsFileName($parent_path,1);
            }      
        }
    
        return $this->setOptionsFileName($file_name);
    }

    /**
     * Resolve component files
     *
     * @return void
     */
    private function resolveComponentFiles()
    {
        // js files
        $this->addComponentFile('js');
        // css file
        $this->addComponentFile('css');
        // html file
        $this->addComponentFile('html');        
    }
}
