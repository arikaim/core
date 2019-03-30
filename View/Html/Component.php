<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\View\Html;

use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\Filesystem\File;
use Arikaim\Core\View\Template;
use Arikaim\Core\System\Url;
use Arikaim\Core\System\Path;
use Arikaim\Core\Interfaces\View\ComponentInterface;

class Component implements ComponentInterface
{
    /**
     *  Component located in template (theme) 
     */
    const TEMPLATE_LOCATION = 1; 
    
    /**
     * Component located in extension
     */
    const EXTENSION_LOCATION = 2;
    
    /**
     * Resolve component location
     */ 
    const RESOLVE_LOCATION = 3; 

    protected $name;
    protected $template_name;
    protected $full_name;
    protected $path;
    protected $type;  
    protected $full_path;
    protected $file_path;
    protected $language;
    protected $html_code;
    protected $error;
    protected $base_path;
    protected $framework;

    protected $files;
    protected $options;
    protected $properties;

    public function __construct($name, $base_path, $language) 
    {
        $this->language = $language;
           
        $this->parseName($name);
        $this->base_path = $base_path;
        $this->resolvePath();
        
        if ($this->type == Self::RESOLVE_LOCATION) {
            $this->resolveComponentLocation();
        }

        $this->error = "";
        $this->files = [];
        $this->options = [];
        $this->properties = [];

        $this->framework = Template::getCurrentFramework();
    }

    public function getTemplateFile()
    {
        $path = ($this->type == Self::EXTENSION_LOCATION) ? $this->template_name . DIRECTORY_SEPARATOR . 'view' : "";
      
        if (isset($this->files['html'][0]['file_name']) == true) {
            return $path . $this->getFilePath() . $this->files['html'][0]['file_name'];
        }
        return false;
    }

    public function getBasePath()
    {
        return $this->base_path;
    }

    public function hasError()
    {
        return (empty($this->error) == true) ? false : true;
    }

    public function hasContent()
    {
        return ($this->getTemplateFile() == false) ? false : true;          
    }

    public function hasProperties()
    {
        if (isset($this->files['properties']) == true) {
            return (count($this->files['properties']) > 0) ? true : false;
        }
        return false;
    }

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

    public function getFullName()
    {
        return $this->full_name;
    }

    public function getProperties()
    {
        return (is_array($this->properties) == true) ? $this->properties : [];
    }

    public function setProperties(array $properties)
    {
        $this->properties = $properties;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFilePath()
    {
        return $this->file_path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getFullPath()
    {
        return $this->full_path;
    }

    public function setFullPath($path)
    {
        $this->full_path = $path;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getTemplateName()
    {
        return $this->template_name;
    }

    public function getLanguage() 
    {
        return $this->language;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getHtmlCode() 
    {
        return $this->html_code;
    }

    public function getOption($option_name, $default = null)
    {
        $option = Arrays::getValue($this->options,$option_name);
        return (empty($option) == true) ? $default : $option;          
    }

    public function setFilePath($path) 
    {
        $this->file_path = $path;
    }

    public function setHtmlCode($code) 
    {
        $this->html_code = $code;
    }

    public function setError($error) 
    {
        $this->error = $error;
    }

    public function isValid()
    {
        $content = 0;
        $content += ($this->hasContent() == true)    ?  1 : 0;
        $content += ($this->hasFiles('js') == true)  ?  1 : 0;
        $content += ($this->hasFiles('css') == true) ?  1 : 0;
        $content += ($this->hasProperties() == true) ?  1 : 0;
        return ($content > 0) ? true : false;
    }

    public function clearContent()
    {
        $this->files['js'] = [];
        $this->files['css'] = [];
        $this->files['html'] = [];
    }

    public function addFiles($files, $file_type)
    {
        if (is_array($files) == false) {
            return false;
        }
        if (isset($this->files[$file_type]) == false) {
            $this->files[$file_type] = [];
        }
        foreach ($files as $file) {
            array_push($this->files[$file_type],$file);           
        }
        return true;            
    }

    public function addComponentFile($file_ext)
    {
        $file_name = $this->getComponentFile($file_ext);
        if ($file_name === false) {
            return false;
        }
        $file = [
            'file_name' => $file_name,
            'path'      => $this->getFilePath(),
            'full_path' => $this->getFullPath(),
            'url'       => $this->getFileUrl($file_name) 
        ];
        return $this->addFile($file,$file_ext);       
    }

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
     * Parse compoentn name 
     *  [extesnon name | template name]:[name path]
     *  for current template  [name path]
     *  [extenstion name] :: [name path]
     * 
     * @param string $name
     * @return void
     */
    protected function parseName($name)
    {
        $this->full_name = $name;
        if (stripos($name,'::') !== false) {
            // extension component
            $tokens = explode('::',$name);     
            $type = Self::EXTENSION_LOCATION;
        } else {
            // template component
            $tokens = explode(':',$name);  
            $type = Self::TEMPLATE_LOCATION;    
        }

        if (isset($tokens[1]) == false) {    
            // component location not set                     
            $this->path = str_replace('.','/',$tokens[0]);            
            $this->template_name = Template::getTemplateName(); 
            $type = Self::RESOLVE_LOCATION;
        } else {
            // 
            $this->path = str_replace('.','/',$tokens[1]);
            $this->template_name = $tokens[0];          
        }

        $this->type = $type;
        $parts = explode('/',$this->path);
        $this->name = end($parts);
    
    }   

    public function getPropertiesFileName() 
    {
        if (isset($this->files['properties']['file_name']) == true) {
            return $this->files['properties']['file_name'];
        }
        return false;
    }

    public function setPropertiesFileName($file_name) 
    {
       // echo $file_name;
        $this->files['properties']['file_name'] = $file_name;          
    }

   
    public function getOptionsFileName()
    {
        if (isset($this->files['options']['file_name']) == true) {
            return $this->files['options']['file_name'];
        }
        return false;
    }

    public function setOptionsFileName($file_name)
    {
        $this->files['options']['file_name'] = $file_name;
    }

    public function toArray()
    {
        return (array)$this;
    }

    public function getUrl()
    {
        switch ($this->type) {
            case Self::TEMPLATE_LOCATION:
                $url = Url::getTemplateUrl($this->template_name);
                break;
            case Self::EXTENSION_LOCATION:
                $url = Url::getExtensionViewUrl($this->template_name);
                break;                    
        }
        return $url . "/" . $this->base_path . "/" . $this->path . "/";
    }

    private function resolveComponentLocation()
    {      
        if (strpos($this->full_path,Path::TEMPLATES_PATH,0) !== false) {        
            $this->type = Self::TEMPLATE_LOCATION;
        } else {
            $this->type = Self::EXTENSION_LOCATION;
        }

        $this->resolveTemplateName();
    }

    private function resolveTemplateName()
    {
        if ($this->type == Self::TEMPLATE_LOCATION) {
            $path = str_replace(Path::TEMPLATES_PATH,"",$this->full_path);
        } else {
            $path = str_replace(Path::EXTENSIONS_PATH,"",$this->full_path);
        }
     
        $parts = explode('/',$path);
        $this->template_name = (isset($parts[0]) == true) ? $parts[0] : Template::getTemplateName();
    }

    protected function resolvePath() 
    {           
        $template_full_path = Path::getTemplatePath($this->template_name,$this->type);
        $template_path = ($this->type != Self::EXTENSION_LOCATION) ? $this->template_name . DIRECTORY_SEPARATOR : DIRECTORY_SEPARATOR;

        $path = $this->getBasePath() . DIRECTORY_SEPARATOR . $this->path . DIRECTORY_SEPARATOR;
        
        $this->full_path = $template_full_path . $path;
        $this->file_path = $template_path . $path;   
    }

    public function getFrameworkPath()
    {
        return (empty($this->framework) == false) ? "." . $this->framework . DIRECTORY_SEPARATOR : "";          
    }

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

    public function getFileUrl($file_name)
    {
        $template_url = Url::getTemplateUrl($this->template_name);
        return $template_url . '/' . str_replace(DIRECTORY_SEPARATOR,'/',$this->base_path . '/'. $this->path) . '/' . $file_name;
    }
}
