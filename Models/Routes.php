<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager;
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Access\Access;

class Routes extends Model  
{
    // route types
    const TYPE_UNKNOW   = 0;
    const TYPE_PAGE     = 1;
    const TYPE_API      = 2;

    // status
    const DISABLED  = 0;
    const ACTIVE    = 1;
    
    protected $fillable = ['name','path','pattern','method','handler_extension','handler_class','handler_method','extension_name','uuid','auth','type'];
    public $timestamps = false;

    public function disableExtensionRoutes($extension_name) 
    {  
        $routes = $this->where('extension_name','=',$extension_name)->get();
        foreach ($routes as $route) {
            $route->status = 0;
            $route->update();
        }
    }

    public function enableExtensionRoutes($extension_name) 
    {  
        $routes = $this->where('extension_name','=',$extension_name)->get();
        foreach ($routes as $route) {
            $route->status = 1;
            $route->update();
        }
    }

    public function getRoutes($status = Self::ACTIVE, $extension_name = null)
    {
        if (Schema::schema()->hasTable('routes') == false) {
            return null;
        } 
        $model = $this;
        if ($status != null) {
            $model = $this->where('status','=',$status);
        }
        if ($extension_name != null) {
            $model = $model->where('extension_name','=',$extension_name);
        }
        $model = $model->get();
        if (is_object($model) == true) {
            return $model->toArray();
        }
        return null;
    }

    public function deleteExtensionRoutes($extension_name)
    {
        $model = $this->where('extension_name','=',$extension_name);
        if (is_object($model) == true) {
            return $model->delete();
        }
        return false;
    }

    public function deleteRoute($method, $path)
    {
        $result = true;
        $model = $this->where('method','=',$method)->where('path','=',$path);
        if (is_object($model) == true) {
            $result = $model->delete();
        }
        return $result;
    }

    public function getRoute($method, $path)
    {
        return $model = $this->where('method','=',$method)->where('path','=',$path)->get();
    }

    public function hasRoute($method, $path)
    {
        $model = $this->where('method','=',$method)->where('path','=',$path)->get();
        if ($model->isEmpty() == true) {
            return false;
        }
        return true;
    }

    public function addRoute($method, $path, $pattern, $handler_class, $handler_method, $extension_name, $auth = 0, $type = Self::TYPE_UNKNOW)
    {
        $result = false;

        $route['method'] = $method;
        $route['path'] = $path;
        $route['pattern'] = $pattern;
        $route['handler_class'] = $handler_class;
        $route['handler_method'] = $handler_method;
        $route['auth'] = $auth;
        $route['type'] = $type;
      //  $route['handler_extension'] = $handler_extension;
        $route['extension_name'] = $extension_name;
        $route['uuid'] = Utils::getUUID();

        if ($this->hasRoute($method,$path) == false) {
            $result = Routes::create($route); 
        }       
        return $result;
    }

    public function addPageRoute($path, $pattern, $handler_class, $handler_menthod, $extension_name, $auth = 0)
    {
        return $this->addRoute("GET",$path, $pattern, $handler_class, $handler_menthod, $extension_name,$auth,Self::TYPE_PAGE);
    }

    public function addApiRoute($method,$path, $pattern, $handler_class, $handler_menthod, $extension_name, $auth = Access::AUTH_JWT)
    {
        return $this->addRoute($method,$path, $pattern, $handler_class, $handler_menthod, $extension_name,$auth, $auth,Self::TYPE_API);
    }

    public static function isValidAuth($auth)
    {
        if (($auth < 0) || ($auth > 4)) return false;
        return true;
    }

    private function isValid($route) 
    {
        if (isset($route['path']) == false) return false;
        if (isset($route['handler_class']) == false) return false;
        if (isset($route['handler_method']) == false) return false;
        if (trim($route['path']) == "") return false;
        if (trim($route['type']) == "") return false;
        if (trim($route['method']) == "") return false;
        if (Self::isValidAuth($route['auth']) == false) return false;
        return true;
    }

}
