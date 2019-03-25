<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Controlers\Api\Ui;

use Arikaim\Core\Arikaim;
use Arikaim\Core\System\System;
use Arikaim\Core\Controlers\ApiControler;
use Arikaim\Core\View\Template;
use Arikaim\Core\Db\Model;
use Arikaim\Core\System\Url;

/**
 * Page Api controler
*/
class Page extends ApiControler 
{
    /**
     * Load html page
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function loadPage($request, $response, $data) 
    {
        $page_name = $data['name'];
        if ($page_name == false) {
            $this->setApiError("Not valid page name!");  
            return $this->getApiResponse(); 
        }
        $component = Arikaim::page()->render($page_name);
        $result['html'] = $component->getHtmlCode();
        $result['css_files']  = Arikaim::page()->properties()->get('include.page.css',[]);
        $result['js_files']   = Arikaim::page()->properties()->get('include.page.js',[]);
        $result['properties'] = json_encode($component->getProperties());
        $this->setApiResult($result);

        return $this->getApiResponse();
    }

    /**
     * Get html page properties 
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function loadPageProperties($request, $response, $data)
    {       
        $page_name = $data->get('name',Arikaim::page()->getCurrent()); 
        
        $result['properties']['page_name'] = $page_name;
        $result['properties']['library'] = Template::getLibraries(); 
        $result['properties']['version']   = System::getVersion(); 
        $result['properties']['framework'] = Template::getFrameworks();

        $loader = Arikaim::session()->get("template.loader");
        if (empty($loader) == false) {
            $loader_code = Arikaim::view()->component()->load($loader);
        } else {
            $loader_code = "";
        }
        $result['properties']['loader'] = $loader_code;
        $result['properties']['default_language'] = Model::Language()->getDefaultLanguage();
        $result['properties']['language'] = Template::getLanguage();
        $result['properties']['site_url'] = Url::ARIKAIM_BASE_URL;

        $this->setApiResult($result);
        return $this->getApiResponse();
    }
}
