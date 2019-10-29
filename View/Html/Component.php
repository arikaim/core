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
    protected $templateName;

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
    protected $fullPath;

    /**
     * File path
     *
     * @var string
     */
    protected $filePath;

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
    protected $htmlCode;

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
    protected $basePath;

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
    protected $optionsFile;

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
     * @param string $basePath
     * @param string $language
     * @param string $optionsFile
     */
    public function __construct($name, $basePath, $language, $optionsFile = null) 
    {
        $this->language = $language;
        $this->optionsFile = $optionsFile;
        $this->basePath = $basePath;
        $this->error = "";
        $this->files['js'] = [];
        $this->files['css'] = [];
        $this->htmlCode = "";

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
        return $this->basePath;
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
                $path = $this->templateName . DIRECTORY_SEPARATOR . 'view';
                break;
            case Self::TEMPLATE_COMPONENT: 
                $path = "";
                break;
            case Self::GLOBAL_COMPONENT: 
                $path = "";
                break;
        }  
        if (isset($this->files['html'][0]['file_name']) == true) {
            return $path . $this->filePath . $this->files['html'][0]['file_name'];
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
     * @param string $fileType
     * @return boolean
     */
    public function hasFiles($fileType = null)
    {
        if ($fileType == null) {
            return (isset($this->files[$fileType]) == true) ? true: false;
        }

        if (isset($this->files[$fileType]) == true) {
            return (count($this->files[$fileType]) > 0) ? true : false;
        }

        return false;
    }

    /**
     * Return files 
     *
     * @param string $fileType
     * @return array
     */
    public function getFiles($fileType = null)
    {
        if ($fileType == null) {
            return $this->files;
        }

        return (isset($this->files[$fileType]) == true) ? (array)$this->files[$fileType] : [];          
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
        return $this->fullPath;
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
        return $this->templateName;
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
        return $this->htmlCode;
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
        $option = Arrays::getValue($this->options,$name);

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
        $this->htmlCode = $code;
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
     * @param string $fileType
     * @return bool
     */
    public function addFiles($files, $fileType)
    {
        if (is_array($files) == false) {
            return false;
        }
        if (isset($this->files[$fileType]) == false) {
            $this->files[$fileType] = [];
        }
        foreach ($files as $file) {
            if (empty($file) == false) {
                array_push($this->files[$fileType],$file);     
            }                  
        }

        return true;            
    }

    /**
     * Add component file
     *
     * @param string $fileExt
     * @return void
     */
    public function addComponentFile($fileExt)
    {
        $fileName = $this->getComponentFile($fileExt);
        if ($fileName === false) {
            return false;
        }
        $file = [
            'file_name' => $fileName,
            'path'      => $this->filePath,
            'full_path' => $this->getFullPath(),
            'url'       => $this->getFileUrl($fileName) 
        ];

        return $this->addFile($file,$fileExt);       
    }

    /**
     * Add file
     *
     * @param string $file
     * @param string $fileType
     * @return void
     */
    public function addFile($file, $fileType)
    {
        if (is_array($file) == false) {
            return false;
        }

        if (isset($this->files[$fileType]) == false) {
            $this->files[$fileType] = [];
        }
        array_push($this->files[$fileType],$file);
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
            $this->templateName = Template::getTemplateName(); 
            $type = Self::TEMPLATE_COMPONENT;        
        } else {
            $this->path = str_replace('.','/',$tokens[1]);
            $this->templateName = $tokens[0];          
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
     * @param string $fileName
     * @return void
     */
    public function setPropertiesFileName($fileName) 
    { 
        $this->files['properties']['file_name'] = $fileName;          
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
     * @param string $fileName
     * @return void
     */
    public function setOptionsFileName($fileName)
    {
        $this->files['options']['file_name'] = $fileName;
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
                $url = Url::getTemplateUrl($this->templateName);
                break;
            case Self::EXTENSION_COMPONENT:
                $url = Url::getExtensionViewUrl($this->templateName);
                break;   
            case Self::GLOBAL_COMPONENT:
                $url = Url::ARIKAIM_VIEW_URL;
                break;                    
        }

        return $url . "/" . $this->basePath . "/" . $this->path . "/";
    }

    /**
     * Return root component name
     *
     * @return string
     */
    public function getRootComponentPath()
    {
        return Path::getTemplatePath($this->templateName,$this->type);
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
     * @param string $fileExt
     * @param string $language
     * @return string|false
     */
    public function getComponentFile($fileExt = "html", $language = "") 
    {         
        $fileName = $this->getName() . $language . "." . $fileExt;
        // try framework path
        $fullFileName = $this->getFullPath() . $this->getFrameworkPath() . $fileName;
        if (File::exists($fullFileName) == true) {
            return $this->getFrameworkPath() . $fileName;
        }
        // try default path 
        $fullFileName = $this->getFullPath() . $fileName;

        return File::exists($fullFileName) ? $fileName : false;
    }

    /**
     * Convert file path to url
     *
     * @param string $fileName
     * @return string
     */
    public function getFileUrl($fileName)
    {
        return $this->getUrl() . $fileName;
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

    /**
     * Get component full path
     *
     * @param integer $type
     * @return string
     */
    public function getComponentFullPath($type)
    {
        $templateFullPath = Path::getTemplatePath($this->templateName,$type); 
        $basePath = (empty($this->basePath) == false) ? $this->basePath : '';
        $path = $basePath . DIRECTORY_SEPARATOR . $this->path . DIRECTORY_SEPARATOR;   
        
        return $templateFullPath . $path;     
    }

    /**
     * Resolve component path
     *
     * @return void
     */
    protected function resolvePath() 
    {                 
        $basePath = (empty($this->basePath) == false) ? DIRECTORY_SEPARATOR . $this->basePath : '';
        $path = $basePath . DIRECTORY_SEPARATOR . $this->path . DIRECTORY_SEPARATOR;   
      
        switch($this->type) {
            case Self::EXTENSION_COMPONENT:
                $templatePath = '';
                break;
            case Self::TEMPLATE_COMPONENT:
                $templatePath = $this->templateName . DIRECTORY_SEPARATOR;
                break;
            case Self::GLOBAL_COMPONENT:
                $templatePath = '';               
                $path = $this->path . DIRECTORY_SEPARATOR; 
                break;
            default:
                $templatePath = "";      
        }
        $this->fullPath = $this->getComponentFullPath($this->type);
        $this->filePath = $templatePath  . $path;  
    }

    /**
     * Resolve properties file name
     *
     * @return void
     */
    private function resolvePropertiesFileName()
    {
        $language = ($this->language != "en") ? "-". $this->language : "";
        $fileName = $this->getComponentFile("json",$language);

        if ($fileName === false) {
            $fileName = $this->getComponentFile("json");
            if ($fileName === false) {
                return false;
            }
        } 
        $this->setPropertiesFileName($this->getFullPath() . $fileName);   
    }

    /**
     * Resolve options file name
     *
     * @param string|null $path
     * @param integer     $iterations
     * @return bool
     */
    private function resolveOptionsFileName($path = null, $iterations = 0)
    {   
        if (empty($path) == true) {
            $path = $this->getFullPath();
        } 
      
        $fileName = $path . $this->optionsFile;
        if (File::exists($fileName) == false) {
            $parentPath = Path::getParentPath($path) . DIRECTORY_SEPARATOR;  
    
            if (empty($parentPath) == false && $iterations == 0) {
                return $this->resolveOptionsFileName($parentPath,1);
            }      
        }
    
        return $this->setOptionsFileName($fileName);
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
