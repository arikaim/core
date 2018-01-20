<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\View;

use Arikaim\Core\Controler;
use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Form\Properties;
use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Html\Template;

class TemplatesManager 
{
    public function scan()
    {
        $items = [];
        $templates_path = Template::getTemplatesPath();
        foreach (new \DirectoryIterator($templates_path) as $file) {
            if ($file->isDot() == true) continue;
            if ($file->isDir() == true) {
                $template_name = $file->getFilename();      
                array_push($items,$template_name);                 
            }
        }  
        return $items;
    }

    public function getTemlateDetails($template_name)
    {   
        $current_template_name = Template::getTemplateName();
        $templates_path = Template::getTemplatesPath();
        $properties = new Properties();
        $property_file = join(DIRECTORY_SEPARATOR,array($templates_path,$template_name,"$template_name.json"));    
        $properties->load($property_file,"template");
        $details['name'] = $properties->get('name',$template_name);
        $details['title'] = $properties->get('title',$template_name);
        $details['version'] = $properties->get('version','1.0'); 
        $details['themes'] = $properties->get('themes',[]); 
        $details['requires'] = $properties->get('include',[]); 
        $details['image'] = $properties->get('image',[]); 
        $details['current_template'] = $current_template_name;
        $details['components'] = $this->getTemplateComponents($template_name);
        $details['macros'] = $this->getTemplateMacros($template_name);
        $details['pages'] = $this->getTemplatePages($template_name);
        return $details;
    }

    public function getTemplateComponents($template_name)
    {   
        $items = [];
        $path = Template::getTemplatePath($template_name) . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR;       
        foreach (new \DirectoryIterator($path) as $file) {
            if ( $file->isDot() == true ) continue;
            if ( $file->isDir() == true ) {
                $item['name'] = $file->getFilename();
                array_push($items,$item);
            }
        }
        return $items;
    }

    public function getTemplateMacros($template_name)
    {
        $items = [];
        $path = Template::getTemplatePath($template_name) . DIRECTORY_SEPARATOR . "macros" . DIRECTORY_SEPARATOR;       
        foreach (new \DirectoryIterator($path) as $file) {
            if ( ($file->isDot() == true) || ($file->isDir() == true) ) continue;
            $file_ext = $file->getExtension();
            if ( ($file_ext != "html") && ($file_ext != "htm") ) continue;           
            
            $item['name'] = str_replace(".$file_ext",'',$file->getFilename());
            array_push($items,$item);            
        }
        return $items;
    }

    public function getTemplatePages($template_name)
    {
        $items = [];
        $path = Template::getTemplatePath($template_name) . DIRECTORY_SEPARATOR . "pages" . DIRECTORY_SEPARATOR;       
        foreach (new \DirectoryIterator($path) as $file) {
            if ( $file->isDot() == true ) continue;
            if ( $file->isDir() == true ) {
                $item['name'] = $file->getFilename();
                array_push($items,$item);
            }
        }
        return $items;
    }

    public function install($template_name) 
    {

    }
    
    public function update($template_name)
    {
        
    }
}
