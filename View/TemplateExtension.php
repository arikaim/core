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
use Arikaim\Core\View\Html\Filters;
use Arikaim\Core\View\TemplateFunction;
use Arikaim\Core\Utils\DateTime;
use Arikaim\Core\Utils\Mobile;
use Arikaim\Core\FileSystem\File;

/**
 *  Template engine functions, filters and tests.
 */
class TemplateExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    public function __construct() 
    {       
    }

    /**
     * Rempate engine global variables
     *
     * @return array
     */
    public function getGlobals() 
    {
        return Template::getVars();
    }

    /**
     * Template engine functions
     *
     * @return array
     */
    public function getFunctions() 
    {
        $template_function = new TemplateFunction();
        $date = new DateTime();
        $errors = Arikaim::errors();
        $mobile = new Mobile();
        
        return array(
            // html components
            new \Twig_SimpleFunction('component', [Arikaim::view()->component(), 'load'], ['needs_environment' => false,'is_safe' => ['html']]),
            new \Twig_SimpleFunction('componentProperties', [$template_function, 'getComponentProperties']),
            // page
            new \Twig_SimpleFunction('getPageJsFiles', ["\\Arikaim\\Core\\View\\Html\\Page", 'getPageJsFiles']),
            new \Twig_SimpleFunction('getPageCssFiles', ["\\Arikaim\\Core\\View\\Html\\Page", 'getPageCssFiles']),
            new \Twig_SimpleFunction('getComponentsJsFiles', ["\\Arikaim\\Core\\View\\Html\\Page", 'getComponentsJsFiles']),
            new \Twig_SimpleFunction('getComponentsCssFiles', ["\\Arikaim\\Core\\View\\Html\\Page", 'getComponentsCssFiles']),
            new \Twig_SimpleFunction('url', ["\\Arikaim\\Core\\View\\Html\\Page", 'getUrl']),
            new \Twig_SimpleFunction('fullUrl', ["\\Arikaim\\Core\\View\\Html\\Page", 'getFullUrl']),
            new \Twig_SimpleFunction('currentUrl', ["\\Arikaim\\Core\\View\\Html\\Page", 'getCurrentUrl']),
            new \Twig_SimpleFunction('isMobile', [$mobile, 'isMobile']),
            // database 
            new \Twig_SimpleFunction('loadData', [$template_function, 'loadData']),
            new \Twig_SimpleFunction('loadExtensionData', [$template_function, 'loadExtensionData']),
            new \Twig_SimpleFunction('loadDataRow', [$template_function, 'loadDataRow']),
            new \Twig_SimpleFunction('createModel', [$template_function, 'createModel']),
            new \Twig_SimpleFunction('createExtensionModel', [$template_function, 'createExtensionModel']),
            new \Twig_SimpleFunction('hasExtension', [$template_function, 'hasExtension']),
            new \Twig_SimpleFunction('condition', ["\\Arikaim\\Core\\Db\\Model", 'createCondition']),
            new \Twig_SimpleFunction('searchCondition', ["\\Arikaim\\Core\\Db\\Model", 'createSearchCondition']),
            new \Twig_SimpleFunction('joinCondition', ["\\Arikaim\\Core\\Db\\Model", 'createJoinCondition']),
            new \Twig_SimpleFunction('orderBy', ["\\Arikaim\\Core\\Db\\Model", 'createOrderBy']),
            new \Twig_SimpleFunction('getTreePath', ["\\Arikaim\\Core\\Db\\Model", 'getTreePath']),
            // other
            new \Twig_SimpleFunction('getFileType', [$template_function, 'getFileType']),
            new \Twig_SimpleFunction('executeMethod', [$template_function, 'executeMethod']),
            new \Twig_SimpleFunction('extension', [$template_function, 'extensionMethod']),
            new \Twig_SimpleFunction('service', [$template_function, 'service']),
            new \Twig_SimpleFunction('callStatic', [$template_function, 'callStatic']),           
            new \Twig_SimpleFunction('getCurrentLanguage', [$template_function, 'getCurrentLanguage']),
            new \Twig_SimpleFunction('getLanguage', ["\\Arikaim\\Core\\View\\Template","getLanguage"]),
            new \Twig_SimpleFunction('getHiddenClass', [$template_function, 'getHiddenClass']),
            new \Twig_SimpleFunction('getOption', [$template_function, 'getOption']),
            new \Twig_SimpleFunction('getOptions', [$template_function, 'getOptions']),
            new \Twig_SimpleFunction('getErrors', [$errors, 'getErrors']),
            // template
            new \Twig_SimpleFunction('getTemplateJsFiles', ["\\Arikaim\\Core\\View\\Template", 'getJsFiles']),
            new \Twig_SimpleFunction('getTemplateCssFiles', ["\\Arikaim\\Core\\View\\Template", 'getCssFiles']),
            new \Twig_SimpleFunction('getThemeFiles', ["\\Arikaim\\Core\\View\\Template", 'getThemeFiles']),
            new \Twig_SimpleFunction('getCurrentTheme', ["\\Arikaim\\Core\\View\\Theme", 'getCurrentTheme']),
            new \Twig_SimpleFunction('getLibraryFiles', ["\\Arikaim\\Core\\View\\Template", 'getLibraryFiles']),
            // date and time
            new \Twig_SimpleFunction('getTimeZonesList', [$date, 'getTimeZonesList']),
            new \Twig_SimpleFunction('timeInterval', [$date, 'getInterval']),
            new \Twig_SimpleFunction('currentYear', [$template_function, 'currentYear']),
            // macros
            new \Twig_SimpleFunction('macro', ["\\Arikaim\\Core\\View\\Template","getMacroPath"]),
            new \Twig_SimpleFunction('extensionMacro', ["\\Arikaim\\Core\\View\\Template","getExtensionMacroPath"]),
            new \Twig_SimpleFunction('systemMacro', ["\\Arikaim\\Core\\View\\Template","getSystemMacroPath"])
        );
    }

    /**
     * Template engine filters
     *
     * @return array
     */
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
            new \Twig_SimpleFilter('jsonDecode', ["\\Arikaim\\Core\\Utils\\Utils", 'jsonDecode']),
            // date time
            new \Twig_SimpleFilter('dateFormat', [$filters, 'dateFormat']),
            new \Twig_SimpleFilter('timeFormat', [$filters, 'timeFormat']),
            // numbers
            new \Twig_SimpleFilter('numberFormat', ["\\Arikaim\\Core\\Utils\\Number", 'format']),
            // files
            new \Twig_SimpleFilter('fileSize', ["\\Arikaim\\Core\\FileSystem\\File", 'getSizeText']),
        );
    }

    /**
     * Template engine tests
     *
     * @return array
     */
    public function getTests() {
        $template_function = new TemplateFunction();
        return array(
            new \Twig_SimpleTest('haveSubItems', [$template_function, 'haveSubItems'])
        );
    }
}
