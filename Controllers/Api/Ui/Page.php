<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Controllers\Api\Ui;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\View\Template\Template;
use Arikaim\Core\Db\Model;
use Arikaim\Core\System\Url;
use Arikaim\Core\View\Html\HtmlComponent;

/**
 * Page Api controller
*/
class Page extends ApiController 
{
    /**
     * Load html page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @param stringnull $pageName
     * @return Psr\Http\Message\ResponseInterface
     */
    public function loadPage($request, $response, $data, $pageName = null) 
    {        
        $pageName = (empty($pageName) == true) ? $this->resolvePageName($request,$data) : $pageName;

        $component = Arikaim::page()->render($pageName);
        $files = Arikaim::page()->properties()->get('include.page.files',[]);

        $result = [
            'html'       => $component->getHtmlCode(),
            'css_files'  => (isset($files['css']) == true) ? $files['css'] : [],
            'js_files'   => (isset($files['js']) == true)  ? $files['js'] : [],
            'properties' => json_encode($component->getProperties())
        ];

        return $this->setResult($result)->getResponse();       
    }

    /**
     * Get html page properties
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function loadPageProperties($request, $response, $data)
    {       
        $pageName = $data->get('name',Arikaim::page()->getCurrent());    
    
        $loader = Template::getLoader(); 
        $loaderCode = (empty($loader) == false) ? HtmlComponent::loadComponent($loader) : "";

        $result['properties'] = [
            'name'              => $pageName,            
            'framework'         => Template::getFrameworks(),
            'loader'            => $loaderCode,  
            'loader_name'       => $loader,
            'library'           => Template::getLibraries(),
            'language'          => Template::getLanguage(),     
            'default_language'  => Model::Language()->getDefaultLanguage(),   
            'site_url'          => Url::ARIKAIM_BASE_URL
        ];

        return $this->setResult($result)->getResponse();       
    }
}
