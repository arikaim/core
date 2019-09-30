<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
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
     * @param string $default_theme
     * @return string
     */
    public static function getCurrentTheme($template_name = null, $default_theme = Self::DEFAULT_THEME_NAME)
    {   
        $template_name = ($template_name == null) ? Template::getTemplateName() : $template_name;         
        try {            
            if (is_object(Arikaim::options()) == false) {
                return $default_theme;
            } 
        } catch(\Exception $e) {
            return $default_theme;
        }
        return Arikaim::options()->get("current.theme.$template_name",$default_theme);
    }

    /**
     * Set current theme
     *
     * @param string $theme_name
     * @param string $template_name
     * @return void
     */
    public static function setCurrentTheme($theme_name, $template_name = null)
    {
        $template_name = (empty($template_name) == true) ? Template::getTemplateName() : $template_name;           
        return Arikaim::options()->set("current.theme.$template_name",$theme_name);     
    }
}
