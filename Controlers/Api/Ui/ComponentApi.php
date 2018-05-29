<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Controlers\Api\Ui;

use Arikaim\Core\Controlers\ApiControler;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Paginator;
use Arikaim\Core\Utils\Utils;

/**
 * Component Api controler
*/
class ComponentApi extends ApiControler
{
    /**
     * get html component details
     *
     * @param object $request
     * @param object $response
     * @param object $args
     * @return object
    */
    public function componentDetails($request, $response, $args)
    {
        // control panel only
        $this->requireControlPanelPermission();

        $component = Arikaim::view()->component()->render($args['name']);
        if ($component->hasError() == true) {
            $this->setApiError($component->getError());
            return $this->getApiResponse();
        }
        $details['properties'] = $component->getProperties();
        $details['options'] = $component->getOptions();
        $details['files'] = $component->getFiles();
        
        $this->setApiResult($details);
        return $this->getApiResponse();
    }

    /**
     * load html component
     *
     * @param object $request
     * @param object $response
     * @param object $args
     * @return object
    */
    public function loadComponent($request, $response, $args)
    {       
        $params = $this->getParams($request);
        $page = $this->getPage($params);

        Paginator::setCurrentPage($page);

        if (isset($params[1]) == true) {
            Paginator::setRowsPerPage($params[1]);
        }
        // get header params
        $header_params = $this->getHeaderParams($request);
        $params = array_merge($params,$header_params);
    
        return $this->load($args['name'],$params);
    }

    private function getPage($params)
    {
        $page = 1;
        if (isset($params[0]) == true) {
            $page = $params[0];
        }
        return $page;
    }

    public function load($component_name,$params = [])
    {   
        $component = Arikaim::view()->component()->render($component_name,$params);
        if ($component->hasError() == true) {
            $this->setApiError($component->getError());
            return $this->getApiResponse();
        }
        $properties = $component->getProperties();
        $options = $component->getOptions();
        $deny_request = $component->getOption('access/deny-request');

        if ($deny_request == true) {
            $this->setApiError('ACCESS_DENIED');
            return $this->getApiResponse();
        }

        $result['html'] = $component->getHtmlCode();
        $result['css_files']  = Arikaim::view()->component()->files()->getArray('css_files');
        $result['js_files']   = Arikaim::view()->component()->files()->getArray('js_files');
        $result['properties'] = json_encode($properties);
       
        $this->setApiResult($result);
        return $this->getApiResponse();
    }

    private function getHeaderParams($request)
    {
        $header_params = null;
        if (isset($request->getHeader('Params')[0]) == true) {
            $header_params = $request->getHeader('Params')[0];
        }
        if ($header_params != null) {
            $header_params = json_decode($header_params,true);
            if (is_array($header_params) == true) {
                return $header_params;
            }
        }
        return [];
    }
}
