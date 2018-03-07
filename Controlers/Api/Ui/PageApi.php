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

class PageApi extends ApiControler 
{
    public function loadPage($request, $response, $args) 
    {
        $page_name = $args['name'];
        return $this->load($page_name);        
    }

    private function load($page_name) 
    {
        if ($page_name == false) {
            $this->setApiError("Not valid page name!");   
        } else {
            $html_code = Arikaim::page()->load($page_name);
            if ($result_code == false) {
                $this->setApiError("Page not exists!");    
            } else {
                $result_code['html'] = $html_code;
                $result_code['css_files']  = $css_files;
                $result_code['js_files']   = $js_files;
                $result_code['properties'] = $properties;
                $this->setApiResult($result_code);
            }

        }
        return $this->getApiResponse();
    }

    public function loadPageProperties($request, $response, $args)
    {       
        if (isset($args['name']) == true) {
            $page_name = $args['name'];
        } else {
            $page_name = Arikaim::page()->getCurrent();
        }
        $result_code['properties']['page_name'] = $page_name;
        $result_code['properties']['libraries'] = Template::getIncludedLibraries(); 
        $result_code['properties']['version']   = System::getVersion(); 

        $loader = Arikaim::session()->get("template.loader");
        if (empty($loader) == false) {
            $loader_code = Arikaim::view()->component()->load($loader);
        } else {
            $loader_code = "";
        }
        $result_code['properties']['loader'] = $loader_code;

        $this->setApiResult($result_code);
        return $this->getApiResponse();
    }
}
