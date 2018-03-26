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

class PageApi extends ApiControler 
{
    public function loadPage($request, $response, $args) 
    {
        $page_name = $args['name'];
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

    public function loadPageProperties($request, $response, $args)
    {       
        if (isset($args['name']) == true) {
            $page_name = $args['name'];
        } else {
            $page_name = Arikaim::page()->getCurrent();
        }
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
        $result['properties']['site_url'] = Arikaim::getBaseUrl();

        $this->setApiResult($result);
        return $this->getApiResponse();
    }
}
