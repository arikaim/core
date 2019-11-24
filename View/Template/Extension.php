<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\View\Template;

use ParsedownExtra;
use Twig\TwigFilter;
use Twig\TwigTest;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Template\TemplateFunction;
use Arikaim\Core\View\Template\Tags\ComponentTagParser;
use Arikaim\Core\View\Template\Tags\AccessTagParser;
use Arikaim\Core\View\Template\Tags\MdTagParser;
use Arikaim\Core\View\Template\Template;
use Arikaim\Core\Utils\Mobile;
use Arikaim\Core\App\Path;
use Arikaim\Core\App\Url;

/**
 *  Template engine functions, filters and tests.
 */
class Extension extends AbstractExtension implements GlobalsInterface
{

    /**
     * Markdown parser
     *
     * @var object
     */
    protected $markdownParser;

    /**
     * Rempate engine global variables
     *
     * @return array
     */
    public function getGlobals() 
    {
        $templateName = Template::getTemplateName();
        $templateUrl = Url::getTemplateUrl($templateName);
        $systemTemplateUrl = Url::getTemplateUrl(Template::SYSTEM_TEMPLATE_NAME);
        return [
            'base_path'             => ARIKAIM_BASE_PATH,
            'base_url'              => Url::ARIKAIM_BASE_URL,
            'template_url'          => $templateUrl,
            'current_template_name' => $templateName,
            'ui_path'               => ARIKAIM_BASE_PATH . Path::ARIKAIM_VIEW_PATH,
            'system_template_url'   => $systemTemplateUrl,
            'system_template_name'  => Template::SYSTEM_TEMPLATE_NAME,
            'ui_library_path'       => Path::LIBRARY_PATH,
            'ui_library_url'        => Url::LIBRARY_URL      
        ];
    }

    /**
     * Template engine functions
     *
     * @return array
     */
    public function getFunctions() 
    {
        $items = Arikaim::cache()->fetch('twig.functions');
        if (is_array($items) == true) {
            return $items;
        }

        $templateFunction = new TemplateFunction();      
        $mobile = new Mobile();
        $items = [
            // html components
            new TwigFunction('component',["Arikaim\\Core\\View\\Html\\HtmlComponent",'load'], ['needs_environment' => true,'is_safe' => ['html']]),
            new TwigFunction('componentProperties',["Arikaim\\Core\\View\\Html\\HtmlComponent",'getProperties']),
            new TwigFunction('componentOptions',[$templateFunction,'getComponentOptions']),
            // page
            new TwigFunction('getPageFiles',[$templateFunction,'getPageFiles']),
            new TwigFunction('getComponentsFiles',[$templateFunction,'getComponentsFiles']),          
            new TwigFunction('url',["Arikaim\\Core\\View\\Html\\Page",'getUrl']),        
            new TwigFunction('currentUrl',["Arikaim\\Core\\View\\Html\\Page",'getCurrentUrl']),
            new TwigFunction('isMobile',[$mobile,'isMobile']),
            // paginator
            new TwigFunction('paginate',['Arikaim\\Core\\Paginator\\SessionPaginator','create']),
            new TwigFunction('clearPaginator',['Arikaim\\Core\\Paginator\\SessionPaginator','clearPaginator']),            
            new TwigFunction('getPaginator',['Arikaim\\Core\\Paginator\\SessionPaginator','getPaginator']),
            new TwigFunction('getRowsPerPage',['Arikaim\\Core\\Paginator\\SessionPaginator','getRowsPerPage']),
            new TwigFunction('getViewType',['Arikaim\\Core\\Paginator\\SessionPaginator','getViewType']),
            // database            
            new TwigFunction('applySearch',['Arikaim\\Core\\Db\\Search','apply']),
            new TwigFunction('createSearch',['Arikaim\\Core\\Db\\Search','setSearchCondition']),
            new TwigFunction('searchValue',['Arikaim\\Core\\Db\\Search','getSearchValue']),
            new TwigFunction('getSearch',['Arikaim\\Core\\Db\\Search','getSearch']),
            new TwigFunction('getOrderBy',['Arikaim\\Core\\Db\\OrderBy','getOrderBy']),
            new TwigFunction('applyOrderBy',['Arikaim\\Core\\Db\\OrderBy','apply']),
            new TwigFunction('createModel',[$templateFunction,'createModel']),
            new TwigFunction('showSql',['Arikaim\\Core\\Db\\Model','getSql']),
            // other
            new TwigFunction('getConstant',["Arikaim\\Core\\Db\\Model",'getConstant']),
            new TwigFunction('hasExtension',[$templateFunction,'hasExtension']),
            new TwigFunction('getFileType',[$templateFunction,'getFileType']),         
            new TwigFunction('system',[$templateFunction,'system']),          
            new TwigFunction('package',['Arikaim\\Core\\Packages\\PackageManagerFactory','create']),       
            new TwigFunction('service',[$templateFunction,'service']),        
            new TwigFunction('getCurrentLanguage',[$templateFunction,'getCurrentLanguage']),
            new TwigFunction('getLanguage',["\\Arikaim\\Core\\View\\Template\\Template","getLanguage"]),
            new TwigFunction('getOption',[$templateFunction,'getOption']),
            new TwigFunction('getOptions',[$templateFunction,'getOptions']),
            new TwigFunction('csrfToken',[$templateFunction,'csrfToken']),                
            new TwigFunction('fetch',["Arikaim\\Core\\App\\Url",'fetch']),
            new TwigFunction('extractArray',[$templateFunction,'extractArray'],['needs_context' => true]),
            // template
            new TwigFunction('getTemplateFiles',["Arikaim\\Core\\View\\Template\\Template",'getTemplateFiles']),
            new TwigFunction('getThemeFiles',["Arikaim\\Core\\View\\Template\\Template",'getThemeFiles']),
            new TwigFunction('getCurrentTheme',["Arikaim\\Core\\View\\Theme",'getCurrentTheme']),
            new TwigFunction('getLibraryFiles',["Arikaim\\Core\\View\\Template\\Template",'getLibraryFiles']),
            new TwigFunction('currentFramework',["Arikaim\\Core\\View\\Template\\Template",'getCurrentFramework']),
            new TwigFunction('loadLibraryFile',[$templateFunction,'loadLibraryFile']),     
            new TwigFunction('loadComponentCssFile',[$templateFunction,'loadComponentCssFile']),             
            // date and time
            new TwigFunction('getTimeZonesList',["Arikaim\\Core\\Utils\\DateTime",'getTimeZonesList']),
            new TwigFunction('timeInterval',['Arikaim\\Core\\Utils\\TimeInterval','getInterval']),
            new TwigFunction('currentYear',[$templateFunction,'currentYear']),
            new TwigFunction('today',["Arikaim\\Core\\Utils\\DateTime",'getCurrentTime']),
            // macros
            new TwigFunction('macro',["Arikaim\\Core\\App\\Path","getMacroPath"]),
            new TwigFunction('extensionMacro',["Arikaim\\Core\\App\\Path","getExtensionMacroPath"]),
            new TwigFunction('systemMacro',["Arikaim\\Core\\App\\Path","getSystemMacroPath"])
        ];
        Arikaim::cache()->save('twig.functions',$items,10);

        return $items;
    }

    /**
     * Template engine filters
     *
     * @return array
     */
    public function getFilters() 
    {       
        $items = Arikaim::cache()->fetch('twig.filters');
        if (is_array($items) == true) {
            return $items;
        }
        $items = [          
            // Html
            new TwigFilter('attr',["Arikaim\\Core\\View\\Template\\Filters",'attr'],['is_safe' => ['html']]),
            new TwigFilter('tag',["Arikaim\\Core\\Utils\\Html",'htmlTag'],['is_safe' => ['html']]),
            new TwigFilter('singleTag',["Arikaim\\Core\\Utils\\Html",'htmlSingleTag'],['is_safe' => ['html']]),
            new TwigFilter('startTag',["Arikaim\\Core\\Utils\\Html",'htmlStartTag'],['is_safe' => ['html']]),
            new TwigFilter('getAttr',["Arikaim\\Core\\Utils\\Html",'getAttributes'],['is_safe' => ['html']]),
            new TwigFilter('decode',["Arikaim\\Core\\Utils\\Html",'specialcharsDecode'],['is_safe' => ['html']]),
            // other
            new TwigFilter('ifthen',["Arikaim\\Core\\View\\Template\\Filters",'is']),
            new TwigFilter('dump',["Arikaim\\Core\\View\\Template\\Filters",'dump']),
            new TwigFilter('string',["Arikaim\\Core\\View\\Template\\Filters",'convertToString']),
            new TwigFilter('emptyLabel',["Arikaim\\Core\\View\\Template\\Filters",'emptyLabel']),
            new TwigFilter('sliceLabel',["Arikaim\\Core\\View\\Template\\Filters",'sliceLabel']),
            new TwigFilter('jsonDecode',["Arikaim\\Core\\Utils\\Utils",'jsonDecode']),
            new TwigFilter('baseClass',["Arikaim\\Core\\Utils\\Utils",'getBaseClassName']),            
            // date time
            new TwigFilter('dateFormat',["Arikaim\\Core\\Utils\\DateTime",'dateFormat']),
            new TwigFilter('dateTimeFormat',["Arikaim\\Core\\Utils\\DateTime",'dateTimeFormat']),
            new TwigFilter('timeFormat',["Arikaim\\Core\\Utils\\DateTime",'timeFormat']),
            // numbers
            new TwigFilter('numberFormat',["Arikaim\\Core\\Utils\\Number",'format']),
            // files
            new TwigFilter('fileSize',["Arikaim\\Core\\FileSystem\\File",'getSizeText']),
            // text
            new TwigFilter('renderText',["Arikaim\\Core\\Utils\\Text",'render']),
            new TwigFilter('sliceText',["Arikaim\\Core\\Utils\\Text",'sliceText']),
            new TwigFilter('titleCase',["Arikaim\\Core\\Utils\\Text",'convertToTitleCase']),
            new TwigFilter('md',[$this,'parseMarkdown']),
        ];

        Arikaim::cache()->save('twig.filters',$items,10);

        return $items;
    }

    /**
     * Template engine tests
     *
     * @return array
     */
    public function getTests() 
    {
        $items = Arikaim::cache()->fetch('twig.tests');
        if (is_array($items) == true) {
            return $items;
        }
        $items = [
            new TwigTest('haveSubItems',["Arikaim\\Core\\Utils\\Arrays",'haveSubItems']),
            new TwigTest('object',["Arikaim\\Core\\View\\Template\\Tests",'isObject']),
            new TwigTest('string',["Arikaim\\Core\\View\\Template\\Tests",'isString']),
            new TwigTest('access',["Arikaim\\Core\\View\\Template\\Tests",'hasAccess']),
            new TwigTest('versionCompare',["Arikaim\\Core\\View\\Template\\Tests",'versionCompare'])
        ];
        Arikaim::cache()->save('twig.tests',$items,10);

        return $items;
    }

    /**
     * Template engine tags
     *
     * @return array
     */
    public function getTokenParsers()
    {
        return [
            new ComponentTagParser(),
            new AccessTagParser(),
            new MdTagParser()
        ];
    }

    /**
     * Parse Markdown
     *
     * @param array $context
     * @param string $content
     * @return string
     */
    public function parseMarkdown($content, $context = [])
    {
        if (empty($this->markdownParser) == true) {
            $this->markdownParser = new ParsedownExtra();
        }

        return $this->markdownParser->text($content);
    }
}
