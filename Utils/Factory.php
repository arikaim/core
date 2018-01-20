<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Utils;

use Arikaim\Core\Extension\Routes;

class Factory 
{
    public static function createInstance($full_class_name, $args = null)
    {
        if (class_exists($full_class_name) == false) {
            $full_class_name = Self::getCoreNamespace() . $full_class_name;
        }
        if (class_exists($full_class_name) == false) return false;
        if ($args != null) {            
            $instance = new $full_class_name(...$args);
        } else {
            $instance = new $full_class_name;
        }
        if (is_object($instance) ) {        
            return $instance;
        }
        return null;
    }

    public static function createExtension($extension_name, $class_name, $args = null)
    {
        $full_class_name = Self::getClassName($extension_name,$class_name);        
        $instance = Self::createInstance($full_class_name,$args);
        
        if ($instance instanceof \Arikaim\Core\Interfaces\ExtensionInterface == true) {           
            return $instance;
        }
        return null;
    }

    public static function createAuthMiddleware($auth, $args = null)
    {
        switch ($auth) {
            case Routes::SESSION_AUTH: {
                $class_name = "SessionAuthentication";
                break;
            }
            case Routes::JWT_AUTH: {
                $class_name = "JwtAuthentication";
                break;
            }
            default: {
                return null;
            }
        }
        return Self::createInstance(Self::getMiddlewareNamespace() . $class_name,$args);
    }

    public static function getCoreNamespace()
    {
        return "\\Arikaim\\Core\\";
    }

    public static function getMiddlewareNamespace()
    {
        return Self::getCoreNamespace() . "Middleware\\";
    }

    public static function getExtensionControlerCallable($extension_name, $base_class, $method = null)
    {
        $class_name = Self::getExtensionControlerClass($extension_name, $base_class);
        $method_call = "";
        if ($method != null) {
            $method_call = ":" . $method;
        }
        return $class_name . $method_call;
    }
    
    public static function getExtensionControlerClass($extension_name, $base_class_name)
    {
        $extension_name = ucfirst($extension_name);
        return Self::getExtensionControlersNamespace($extension_name) . "\\" . $base_class_name;
    }

    public static function getExtensionControlersNamespace($extension_name)
    {
        $extension_name = ucfirst($extension_name);
        return "\\Arikaim\\Extensions\\$extension_name\\Controlers";
    }

    public static function getExtensionNamespace($extension_name) 
    {   
        $extension_name = ucfirst($extension_name);
        return Self::getExtensionsNamespace() . $extension_name;
    }

    public static function getClassName($extension_name, $base_class_name)
    {
        return Self::getExtensionNamespace($extension_name) . "\\" . $base_class_name;
    }

    public static function getExtensionsNamespace()
    {
        return "\\Arikaim\\Extensions\\";
    }
}
