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
use Arikaim\Core\View\Template;
use Arikaim\Core\Interfaces\View\ComponentInterface;

class Component implements ComponentInterface
{
    protected $name;
    protected $template_name;
    protected $full_name;
    protected $path;
    protected $type;
    protected $extension_name;
    protected $full_path;
    protected $file_path;
    protected $language;
    protected $html_code;
    protected $error;
  
    protected $files;
    protected $options;
    protected $properties;

    public function __construct($name, $language = null) 
    {
        if ($language == null) {
            $this->language = Template::getLanguage();
        } else {
            $this->language = $language;
        }

        $this->parseName($name);
        $this->error = "";
        $this->files = [];
        $this->options = [];
        $this->properties = [];
    }

    public function getTemplateFile()
    {
        if (isset($this->files['html'][0]['file_name']) == true) {
            return $this->getFilePath() . $this->files['html'][0]['file_name'];
        }
        return false;
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

    public function getFiles($file_type = null)
    {
        if ($file_type == null) {
            return $this->files;
        }

        if (isset($this->files[$file_type]) == true) {
            return $this->files[$file_type];
        }
        return [];
    }

    public function getFullName()
    {
        return $this->full_name;
    }

    public function getProperties()
    {
        return $this->properties;
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

    public function getExtensionName() 
    {
        return $this->extension_name;
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
        if (empty($option) && $default !== null) {
            $option = $default;
        }
        return $option;
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

    protected function parseName($name)
    {
        $name_parts = explode(':',$name);
        $this->template_name = Template::getTemplateName();
        $this->full_name = $name;

        if (isset($name_parts[1]) == false) {                                
            $this->path = str_replace('.','/',$name_parts[0]);            
            $this->type = Template::USER;
            $this->extension_name = ""; 
        } else {
            $this->extension_name = $name_parts[0];
            $this->template_name = $name_parts[0];
            $this->path = str_replace('.','/',$name_parts[1]);
            $this->type = Template::EXTENSION;
        }

        $parts = explode('/',$this->path);
        $this->name = end($parts);

        // parse path
        $path_parts = explode('#',$this->path);
        if (isset($path_parts[1]) == true) {
            $this->template_name = $path_parts[0];
            $this->path  = $path_parts[1];
            $this->type = Template::USER;
        } 

        if ($this->extension_name == Template::SYSTEM_TEMPLATE_NAME) {
            $this->template_name = Template::SYSTEM_TEMPLATE_NAME;
            $this->extension_name = "";
            $this->type = Template::SYSTEM;            
        }       
        return true;
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
        return (array) $this;
    }
}
