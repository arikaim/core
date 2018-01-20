<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Extension;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Utils\Collection;

class Routes extends Collection 
{
    const NO_AUTH             = 0;
    const BASIC_AUTH          = 1;
    const SESSION_AUTH        = 2;
    const JWT_AUTH            = 3;
    const CUSTOM_TOKEN_AUTH   = 4;

    public function addRoute($method, $path, $pattern, $handler_class, $handler_menthod = "", $auth = 0, $handler_extension = null)
    {        
        $route['method'] = $method;
        $route['path'] = $path;
        $route['pattern'] = $pattern;
        $route['handler_class'] = $handler_class;
        $route['handler_method'] = $handler_menthod;
        $route['auth'] = $auth;
        $route['handler_extension'] = $handler_extension;

        if ($this->isValid($route) == true) {
            array_push($this->data,$route);
            return true;
        }       
        return false;
    }

    private function isValid($route) 
    {
        if (isset($route['path']) == false) return false;
        if (isset($route['handler_class']) == false) return false;
        if (isset($route['handler_method']) == false) return false;
        if (trim($route['path']) == "") return false;
        if (Self::isValidAuth($route['auth']) == false) return false;
        return true;
    }

    public function getRoute($path, $method) 
    {     
        $model = Model::Routes();
        $routes = $model->where('path','=',$path)->get()->toArray();
        foreach ($routes as $key => $item) {
            $methods = explode(',',$method);
            if (in_array($method,$methods) == true) {               
                $item['callable'] = Factory::getExtensionControlerCallable($item['extension_name'],$item['handler_class'],$item['handler_method']);
                return $item;
            }
        }
        return false;
    }

    public static function isValidAuth($auth)
    {
        if (($auth < 0) || ($auth > 4)) return false;
        return true;
    }

    public static function getAuthTitle($auth)
    {
        switch($auth) {
            case Self::NO_AUTH : return "";
            case Self::BASIC_AUTH : return "Basic";
            case Self::SESSION_AUTH : return "Session";
            case Self::JWT_AUTH : return "JWT";
            case Self::CUSTOM_TOKEN_AUTH : return "CWT";
        }
        return "";
    }
}
