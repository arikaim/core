<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\View;

use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Template\Template;

/**
 * Template theme
 */
class Theme 
{
    /**
     *  Default theme name
     */
    const DEFAULT_THEME_NAME = 'default';

    /**
     * Return current template theme
     *
     * @param string $template_name
     * @param string $defaultTheme
     * @return string
     */
    public static function getCurrentTheme($templateName = null, $defaultTheme = Self::DEFAULT_THEME_NAME)
    {   
        $templateName = ($templateName == null) ? Template::getTemplateName() : $templateName;         
        try {            
            if (is_object(Arikaim::options()) == false) {
                return $defaultTheme;
            } 
        } catch(\Exception $e) {
            return $defaultTheme;
        }

        return Arikaim::options()->get("current.theme.$templateName",$defaultTheme);
    }

    /**
     * Set current theme
     *
     * @param string $theme
     * @param string $templateName
     * @return void
     */
    public static function setCurrentTheme($theme, $templateName = null)
    {
        $templateName = (empty($templateName) == true) ? Template::getTemplateName() : $templateName; 

        return Arikaim::options()->set("current.theme.$templateName",$theme);     
    }
}
