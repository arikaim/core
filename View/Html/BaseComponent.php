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

use Twig\Environment;

use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Template\Template;
use Arikaim\Core\Utils\Mobile;
use Arikaim\Core\View\Html\Component;
use Arikaim\Core\System\Url;
use Arikaim\Core\Interfaces\View\ComponentInterface;

/**
 *  Base html component
 */
class BaseComponent   
{
    /**
     * Fetch component
     *
     * @param Environment $env
     * @param ComponentInterface $component
     * @param array $params
     * @return Component
     */
    public static function fetch(Environment $env, ComponentInterface $component, $params = [])
    {
        if (empty($component->getTemplateFile()) == true) {
            return $component;
        }
        $template = $env->loadTemplate($component->getTemplateFile());
    
        $code = $env->render($component->getTemplateFile(),$params);
        $component->setHtmlCode($code);         
        return $component;
    }

    /**
     * Procss component options
     *
     * @param ComponentInterface $component
     * @return Arikaim\Core\Interfaces\View\ComponentInterface
     */
    public static function processOptions(ComponentInterface $component)
    {        
        $error = false;       
        // check auth access 
        $auth = $component->getOption('access/auth');
        if (empty($auth) == false && strtolower($auth) != 'none') {
            if (Arikaim::auth()->isLogged() == false) {
                $error = Arikaim::errors()->getError("ACCESS_DENIED");
            }
        }
        // check permissions
        $permission = $component->getOption('access/permission');       
        if (empty($permission) == false) {
            if (Arikaim::access()->hasAccess($permission) == false) {
                $error = Arikaim::errors()->getError("ACCESS_DENIED");
            }          
        }    
        
        $component = Self::applyIncludeOption($component,'include/js','js');
        $component = Self::applyIncludeOption($component,'include/css','css');

        // mobile only option
        $mobile_only = $component->getOption('mobile-only');      
        if ($mobile_only == "true") {
            if (Mobile::mobile() == false) {    
                $component->clearContent();               
            }
        }

        if ($error !== false) {
            $error = Arikaim::getError("TEMPLATE_COMPONENT_ERROR",["full_component_name" => $component->getName(),'details' => $error]);
            $component->setError($error);
        }
        return $component;
    }

    /**
     * Apply component include option
     *
     * @param Component $component
     * @param string $option_key
     * @param string $file_type
     * @return Component
     */
    public static function applyIncludeOption($component, $option_key, $file_type)
    { 
        $option = $component->getOption($option_key);   
       
        if (empty($option) == false) {
            if (is_array($option) == true) {              
                // include component files
                foreach ($option as $item) {                   
                    if (Url::isValid($item) == true) {  
                        $files = [['url' => $item,'params' => ['external' => true] ]];                  
                    } else {
                        $files = Self::getComponentFiles($item,$file_type);
                    }                      
                    $component->addFiles($files,$file_type);
                }
            } else {               
                if (Url::isValid($option) == true) {
                    $files = [['url' => $option,'params' => ['external' => true]]];   
                    print_r($files);           
                } else {
                    $files = Self::getComponentFiles($option,$file_type);
                }            
                $component->addFiles($files,$file_type);
            }
        }
        return $component;
    }

    /**
     * Return compoenent files
     *
     * @param string $name
     * @param string $file_type
     * @return array
     */
    public static function getComponentFiles($name, $file_type = null)
    {
        $component = static::createComponent($name,'components');
        return (is_object($component) == true) ? $component->getFiles($file_type) : ['js' => [],'css' => []];
    }

    /**
     * Create component
     *
     * @param string $name
     * @param string $base_path
     * @param string $language
     * @param boolean $with_options
     * @param string $options_file
     * @return ComponentInterface
     */
    protected static function createComponent($name, $base_path, $language = null, $with_options = true, $options_file = 'component.json')
    {
        $language = (empty($language) == true) ? Template::getLanguage() : $language;
        $component = new Component($name,$base_path,$language,$options_file);
    
        if ($component->isValid() == false) {           
            return $component->setError(Arikaim::getError("TEMPLATE_COMPONENT_NOT_FOUND",["full_component_name" => $name]));           
        }
       
        return ($with_options == true) ? Self::processOptions($component) : $component;         
    }

    /**
     * Return true if component name is full name
     *
     * @param string $name
     * @return boolean
     */
    public static function isFullName($name)
    {
        return (stripos($name,':') !== false || stripos($name,'>') !== false) ? true : false;          
    } 
}
