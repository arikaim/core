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
use Arikaim\Core\View\Html\Component;
use Arikaim\Core\Utils\Utils;

class ComponentApi extends ApiControler 
{
    public function loadComponent($request, $response, $args) 
    {  
        $params = $this->getParams($request);
        if (isset($params[0]) == true) {
            Paginator::setCurrentPage($params[0]);
        }
        if (isset($params[1]) == true) {
            Paginator::setRowsPerPage($params[1]);
        }
        // get header params
        $header_params = $this->getHeaderParams($request);
        $params = array_merge($params,$header_params);
    
        return $this->load($args['name'],$params);
    }

    public function load($component_name,$params = []) 
    {   
        $component = new Component();
        $html_code = $component->fetch($component_name,$params);
        $components = Arikaim::templateComponents();

        $css_files = $components->getIncludeFiles('css_files');
        $js_files = $components->getIncludeFiles('js_files');
        $properties = Utils::jsonEncode($components->getProperties($component_name));

        if (($html_code == false) && ($js_files == false) && ($css_files == false)) {
            $this->setApiError("Component not exists!");    
        } else {
            $result_code['html'] = $html_code;
            $result_code['css_files']  = $css_files;
            $result_code['js_files']   = $js_files;
            $result_code['properties'] = $properties;
            $this->setApiResult($result_code);
        }
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
