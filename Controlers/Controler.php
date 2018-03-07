<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Controlers;

use Arikaim\Core\Arikaim;

class Controler
{
    const PAGE = 1;
    const API  = 2;

    protected $type;

    public function __construct()
    { 
        $this->type = Controler::PAGE;
    }

    public function getParams($request)
    {
        $params = explode('/', $request->getAttribute('params'));
        $params = array_filter($params);
        $vars = $request->getQueryParams();
        $result = array_merge($params, $vars);
        return $result;
    }

    public function requireControlPanelPermission()
    {
        return $this->requirePermission(Arikaim::access()->get("CONTROL_PANEL"),Arikaim::access()->get("FULL"));
    }
    
    public function requirePermission($name, $access)
    {
        $result = Arikaim::access()->hasPermission($name,$access);  
        if ($result == true) {
            return true;
        }
        // access denied response
        switch ($this->type) {
            case Controler::API: {            
                $response = $this->api_response->displayAuthError();           
                Arikaim::getApp()->respond($response); 
                Arikaim::end();       
            }   
            default: {
                $response = Arikaim::page()->load("system:system-error",$args);
                Arikaim::getApp()->respond($response); 
                Arikaim::end(); 
            }         
        }
        return false;
    }

    public static function getControlersNamespace()
    {
        return "Arikaim\\Core\\Controlers";
    }

    public static function getApiControlersNamespace()
    {
        return Self::getControlersNamespace() . "\\Api";
    }

    public static function getControlerClass($class_name)
    {
        return Self::getControlersNamespace() . "\\" . $class_name;
    }
}
