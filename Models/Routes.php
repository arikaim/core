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

class Routes extends Model  
{
    // route auth type
    const AUTH_NONE         = 0;
    const AUTH_BASIC        = 1;
    const AUTH_SESSION      = 2;
    const AUTH_JWT          = 3;
    const AUTH_CUSTOM_TOKEN = 4;

    // route types
    const TYPE_UNKNOW   = 0;
    const TYPE_PAGE     = 1;
    const TYPE_API      = 2;

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

    public function getRoutes()
    {
        if (Schema::schema()->hasTable('routes') == false) {
            return null;
        } 
        $model = $this->where('status','=',1)->get();
        if (is_object($model) == true) {
            return $model->toArray();
        }
        return null;
    }

    public function addRoute($method, $path, $pattern, $handler_class, $handler_menthod = "", $auth = 0, $extension = null, $type = 0)
    {

    }

    public function appPageRoute($path, $pattern, $handler_class, $handler_menthod = "", $extension = null)
    {

    }

    public function appApiRoute($method,$path, $pattern, $handler_class, $handler_menthod = "", $extension = null, $auth = Routes::AUTH_JWT)
    {

    }
}
