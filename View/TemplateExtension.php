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

use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Html\Component;
use Arikaim\Core\View\Html\Page;
use Arikaim\Core\View\Html\Template;
use Arikaim\Core\View\Html\Filters;
use Arikaim\Core\View\TemplateFunction;
use Arikaim\Core\Utils\DateTime;
use Arikaim\Core\Utils\Number;

class TemplateExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    public function __construct() 
    {
       
    }

    public function getGlobals() 
    {
        return Arikaim::getTemplateVars();
    }

    public function getFunctions() 
    {
        $component = new Component();
        $page = new Page();
        $template = new Template();    
        $template_function = new TemplateFunction();
        $date = new DateTime();
        $errors = Arikaim::errors();
        
        return array(
            // html components
            new \Twig_SimpleFunction('component', [$component, 'loadComponent'], ['needs_environment' => true,'is_safe' => ['html']]),
            // return data only
            // component
            new \Twig_SimpleFunction('componentProperties', [$component, 'getComponentProperties']),
            new \Twig_SimpleFunction('getComponentsJSFiles', [$component, 'getComponentsJSFiles']),
            new \Twig_SimpleFunction('getComponentsCSSFiles', [$component, 'getComponentsCSSFiles']),
            // database 
            new \Twig_SimpleFunction('loadData', [$template_function, 'loadData']),
            new \Twig_SimpleFunction('loadExtensionData', [$template_function, 'loadExtensionData']),
            new \Twig_SimpleFunction('loadDataRow', [$template_function, 'loadDataRow']),
            new \Twig_SimpleFunction('loadExtensionDataRow', [$template_function, 'loadExtensionDataRow']),
            new \Twig_SimpleFunction('createModel', [$template_function, 'createModel']),
            new \Twig_SimpleFunction('createExtensionModel', [$template_function, 'createExtensionModel']),
            // other
            new \Twig_SimpleFunction('getFileType', [$template_function, 'getFileType']),
            new \Twig_SimpleFunction('execute', [$template_function, 'execute']),
            new \Twig_SimpleFunction('executeMethod', [$template_function, 'executeMethod']),
            new \Twig_SimpleFunction('currentYear', [$template_function, 'currentYear']),
            new \Twig_SimpleFunction('getCurrentLanguage', [$template_function, 'getCurrentLanguage']),
            new \Twig_SimpleFunction('getLanguageCode', ["\\Arikaim\\Core\\Arikaim","getLanguage"]),
            new \Twig_SimpleFunction('getHiddenClass', [$template_function, 'getHiddenClass']),
            new \Twig_SimpleFunction('getOption', [$template_function, 'getOption']),
            new \Twig_SimpleFunction('getOptions', [$template_function, 'getOptions']),
            new \Twig_SimpleFunction('getErrors', [$errors, 'getErrors']),
            // page
            new \Twig_SimpleFunction('getPageJSFiles', [$page, 'getPageJSFiles']),
            new \Twig_SimpleFunction('getPageType', [$page, 'getPageType']),
            new \Twig_SimpleFunction('getPageCSSFiles', [$page, 'getPageCSSFiles']),
            // template
            new \Twig_SimpleFunction('getTemplateJSFiles', [$template, 'getTemplateJSFiles']),
            new \Twig_SimpleFunction('getTemplateCSSFiles', [$template, 'getTemplateCSSFiles']),
            new \Twig_SimpleFunction('getTheme', [$template, 'getTheme']),
            new \Twig_SimpleFunction('getLibraryFiles', [$template, 'getLibraryFiles']),
            // date and time
            new \Twig_SimpleFunction('getTimeZonesList', [$date, 'getTimeZonesList'])
        );
    }

    public function getFilters() 
    {
        $filters = new Filters();
        return array(
            new \Twig_SimpleFilter('attr', [$filters, 'attr'],['is_safe' => ['html']]),
            new \Twig_SimpleFilter('tag', [$filters, 'htmlTag'],['is_safe' => ['html']]),
            new \Twig_SimpleFilter('singleTag', [$filters, 'htmlSingleTag'],['is_safe' => ['html']]),
            new \Twig_SimpleFilter('startTag', [$filters, 'htmlStartTag'],['is_safe' => ['html']]),
            new \Twig_SimpleFilter('getAttr', [$filters, 'getAttributes'],['is_safe' => ['html']]),
            new \Twig_SimpleFilter('showArray', [$filters, 'showArray'],['is_safe' => ['html']]),
            new \Twig_SimpleFilter('ifthen', [$filters, 'is']),
            // date time
            new \Twig_SimpleFilter('dateFormat', [$filters, 'dateFormat']),
            new \Twig_SimpleFilter('timeFormat', [$filters, 'timeFormat']),
            // numbers
            new \Twig_SimpleFilter('numberFormat', ["\\Arikaim\\Core\\Utils\\Number", 'format']),  
        );
    }

    public function getTests() {
        $template_function = new TemplateFunction();
        return array(
            new \Twig_SimpleTest('haveSubItems', [$template_function, 'haveSubItems'])
        );
    }
}
