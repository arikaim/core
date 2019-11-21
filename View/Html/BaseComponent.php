<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\View\Html;

use Twig\Environment;

use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Template\Template;
use Arikaim\Core\Utils\Mobile;
use Arikaim\Core\View\Html\Component;
use Arikaim\Core\App\Url;
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
        $env->loadTemplate($component->getTemplateFile());
    
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
        $mobileOnly = $component->getOption('mobile-only');      
        if ($mobileOnly == "true") {
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
     * @param string $key
     * @param string $fileType
     * @return Component
     */
    public static function applyIncludeOption($component, $key, $fileType)
    { 
        $option = $component->getOption($key);   
       
        if (empty($option) == false) {
            if (is_array($option) == true) {              
                // include component files
                foreach ($option as $item) {                      
                    $files = Self::resolveIncludeFile($item,$fileType);
                    $component->addFiles($files,$fileType);
                }
            } else {   
                $files = Self::resolveIncludeFile($option,$fileType);                        
                $component->addFiles($files,$fileType);
            }
        }
        
        return $component;
    }

    /**
     * Resolve include file
     *
     * @param string $includeFile
     * @param string $fileType
     * @return array
     */
    protected static function resolveIncludeFile($includeFile, $fileType)
    {
        if (Url::isValid($includeFile) == true) {             
            $tokens = explode('|',$includeFile);
            $url = $tokens[0];
            $tokens[0] = 'external';
            $params = (isset($tokens[1]) == true) ? $tokens : [];                           
            $files = [['url' => $url,'params' => $params]];       
        } else {
            $files = Self::getComponentFiles($includeFile,$fileType);
        }

        return $files;
    }

    /**
     * Return compoenent files
     *
     * @param string $name
     * @param string $fileType
     * @return array
     */
    public static function getComponentFiles($name, $fileType = null)
    {
        $component = static::createComponent($name,'components');

        return (is_object($component) == true) ? $component->getFiles($fileType) : ['js' => [],'css' => []];
    }

    /**
     * Create component
     *
     * @param string $name
     * @param string $basePath
     * @param string $language
     * @param boolean $withOptions
     * @param string $optionsFile
     * @return ComponentInterface
     */
    protected static function createComponent($name, $basePath, $language = null, $withOptions = true, $optionsFile = 'component.json')
    {
        $language = (empty($language) == true) ? Template::getLanguage() : $language;
        $component = new Component($name,$basePath,$language,$optionsFile);
        if ($component->isValid() == false) {           
            $component->setError(Arikaim::getError("TEMPLATE_COMPONENT_NOT_FOUND",["full_component_name" => $name]));  
            return $component;         
        }
       
        return ($withOptions == true) ? Self::processOptions($component) : $component;         
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
