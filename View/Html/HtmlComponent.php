<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\View\Html;

use Twig\Environment;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\View\Html\BaseComponent;

/**
 * Render html component
 */
class HtmlComponent extends BaseComponent
{
    /**
     * Rnder component error mesage
     *
     * @param string $message
     * @return string
     */
    public static function getErrorMessage($message)
    {      
        return Self::loadComponent('components:message.error',['message' => $message]);
    }

    /**
     * Load 
     *
     * @param string $name
     * @param array $params
     * @param string|null $language
     * @return string
     */
    public static function loadComponent($name, $params = [], $language = null)
    {
        return Self::load(Arikaim::view()->getEnvironment(),$name,$params,$language);
    }

    /**
     * Load component from template
     *
     * @param Environment $env
     * @param string $name
     * @param array $params
     * @param string|null $language
     * @return string
     */
    public static function load(Environment $env, $name, $params = [], $language = null)
    {              
        $component = Self::render($env,$name,$params,$language);
        if ($component == null) {
            if (Arrays::getDefaultValue($params,'show_error') !== false) {              
                return Self::getErrorMessage('Not valid component name ' .  $name);
            }
            return '';
        }
        if ($component->hasError() == true) {
            return Self::getErrorMessage($component->getError());
        }

        return $component->getHtmlCode();
    }

    /**
     * Load component with conetext array
     *
     * @param array $context
     * @param Environment $env
     * @param string $name
     * @param array $params
     * @param string|null $language
     * @return void
     */
    public static function loadWithContext(&$context, Environment $env, $name, $params = [], $language = null)
    {
        if (is_array($context) == true) {
            $params = array_merge($context,$params);
        }

        return Self::load($env,$name,$params,$language);
    }

    /**
     * Create
     *
     * @param string $name
     * @param string|null $language
     * @param boolean $withOptions
     * @return Component
     */
    public static function create($name, $language = null, $withOptions = true)
    {
        return Self::createComponent($name,'components',$language,$withOptions);
    }

    /**
     * Render component
     *
     * @param string|null $name
     * @param array $params
     * @param string|null $language
     * @param boolean $withOptions
     * @return Component
     */
    public static function renderComponent($name, $params = [], $language = null, $withOptions = true) 
    { 
        return Self::render(Arikaim::view()->getEnvironment(),$name,$params,$language,$withOptions);
    }

    /**
     * Render component html code from template
     *
     * @param Environment $env
     * @param string $name
     * @param array $params
     * @param string|null $language
     * @param boolean $withOptions
     * @return Component
     */
    public static function render(Environment $env, $name, $params = [], $language = null, $withOptions = true) 
    {    
        $component = Self::create($name,$language,$withOptions);
        if (is_object($component) == false) {
            return null;               
        }
      
        if ($component->hasError() == true) {
            return $component;
        }
        // default params      
        $params['component_url'] = $component->getUrl();

        $params = Arrays::merge($component->getProperties(),$params);
        $component->setHtmlCode("");  
        if ($component->getOption('render') !== false) {                 
            $component = Self::fetch($env,$component,$params);
            // include files
            Self::includeComponentFiles($component->getFiles('js'),'js');
            Self::includeComponentFiles($component->getFiles('css'),'css');
        }
        $env->addGlobal('current_component_name',$name);
        
        return $component;
    }

    /**
     * Inlcude componnent files
     *
     * @param array $files
     * @param string $key
     * @return boolean
     */
    public static function includeComponentFiles($files, $key)
    {
        if (empty($files) == true) {
            return false;
        }       
        foreach ($files as $item) {             
            Arikaim::page()->properties()->prepend('include.components.files',$item,$key);                    
        }

        return true;
    }

    /**
     * Get properties
     *
     * @param string $name
     * @param string|null $language
     * @return Properties|null
     */
    public static function getProperties($name, $language = null)
    {       
        $component = Self::create($name,$language);

        return (is_object($component) == true) ? $component->loadProperties() : null;
    }
    
    /**
     * Get component options
     *
     * @param string $name   
     * @return array|null
     */
    public static function getOptions($name)
    {       
        $component = Self::create($name);
        
        return (is_object($component) == true) ? $component->getOptions() : null;
    }
}
