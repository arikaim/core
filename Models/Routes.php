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
use Arikaim\Core\Db\Schema;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Access\Access;
use Arikaim\Core\Arikaim;

/**
 * Routes db table model
 */
class Routes extends Model  
{
    // route types
    const TYPE_UNKNOW   = 0;
    const TYPE_PAGE     = 1;
    const TYPE_API      = 2;

    // status
    const DISABLED  = 0;
    const ACTIVE    = 1;
    
    protected $fillable = [
        'id',
        'name',
        'pattern',
        'method',
        'handler_class',
        'handler_method',
        'extension_name',
        'uuid',
        'auth',
        'type',
        'template_name',
        'required_permission',
        'permission_type',
        'template_page'];
        
    public $timestamps = false;

    public function disableExtensionRoutes($extension_name) 
    {  
        $routes = $this->where('extension_name','=',$extension_name)->get();
        foreach ($routes as $route) {
            $route->status = 0;
            // fire event        
            Arikaim::event()->trigger('core.route.disable',$route->toArrray());
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
        if (Schema::hasTable($this) == false) {
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

    public function deleteTemplateRoutes($template_name)
    {
        $model = $this->where('template_name','=',$template_name);
        if (is_object($model) == true) {
            return $model->delete();
        }
        return false;
    }

    public function deleteRoute($method, $pattern)
    {
        $result = true;
        $model = $this->where('method','=',$method)->where('pattern','=',$pattern);
        if (is_object($model) == true) {
            $result = $model->delete();
        }
        return $result;
    }

    public function getRoute($method, $pattern)
    {
        $model = $this->where('method','=',$method);
        $model = $model->where('pattern','=',$pattern)->first();
        if (is_object($model) == false) {
            return false;
        }
        return $model;
    }

    public function getTemplateRoute($pattern, $template_name)
    {
        $pattern .= $this->getLanguagePattern($pattern);       
        $model = $this->where('pattern','=',$pattern);
        $model = $model->where('template_name','=',$template_name)->first();
        if (is_object($model) == false) {
            return false;
        }
        return $model;
    }

    public function getPageRoute($method, $pattern)
    {
        $pattern .= $this->getLanguagePattern($pattern);
        return $this->getRoute($method,$pattern);       
    }

    public function findRoute($condition)
    {
        if (is_array($condition) == false) {
            return null;
        }
        $model = Model::applyCondition($this,$condition);
        $model = $model->get();
        if (is_object($nodel) == false) {
            return false;
        }
        return $model;
    }

    public function hasRoute($method, $pattern)
    {
        $model = $this->getRoute($method, $pattern);
        return ($model == false) ? false : true; 
    }

    public function addRoute(array $route)
    {
        $result = false;
        $route['uuid'] = Utils::getUUID();

        if ($this->hasRoute($route['method'],$route['pattern']) == false) {
            $result = Routes::create($route); 
        }       
        return $result;
    }

    public function addTemplateRoute($pattern, $handler_class, $handler_method, $template_name, $template_page)
    {
        $route['method'] = "GET";
        $route['pattern'] = $pattern . Self::getLanguagePattern($pattern);
        $route['handler_class'] = $handler_class;
        $route['handler_method'] = $handler_method;
        $route['auth'] = Access::AUTH_NONE;
        $route['type'] = Self::TYPE_PAGE;
        $route['template_page'] = $template_page;
        $route['template_name'] = $template_name;
        // fire event        
        Arikaim::event()->trigger('core.route.register',$route);

        return $this->addRoute($route);
    }

    public function addPageRoute($pattern, $handler_class, $handler_method, $extension_name, $auth = Access::AUTH_NONE)
    {
        $route['method'] = "GET";
        $route['pattern'] = $pattern . Self::getLanguagePattern($pattern);
        $route['handler_class'] = $handler_class;
        $route['handler_method'] = $handler_method;
        $route['auth'] = $auth;
        $route['type'] = Self::TYPE_PAGE;
        $route['extension_name'] = $extension_name;
        return $this->addRoute($route);
    }

    public function getLanguagePattern($pattern)
    {        
        return (substr($pattern,-1) == "/") ?  "[{language:[a-z]{2}}/]" : "[/{language:[a-z]{2}}/]";
    }

    public function addApiRoute($method, $pattern, $handler_class, $handler_method, $extension_name, $auth = Access::AUTH_JWT)
    {
        $route['method'] = $method;
        $route['pattern'] = $pattern;
        $route['handler_class'] = $handler_class;
        $route['handler_method'] = $handler_method;
        $route['auth'] = $auth;
        $route['type'] = Self::TYPE_API;
        $route['extension_name'] = $extension_name;
        return $this->addRoute($route);
    }

    public function setPermission($id, $permission_name, array $permission)
    {
        if (is_integer($id) == true) {
            $model = $this->where('id','=',$id)->first();
        }
        if (is_string($id) == true) {
            $model = $this->where('uuid','=',$id)->first();
        }
        if (is_object($model) == false) {
            return false;
        }
        $model->required_permission = $permission_name;
        $model->permission_type = json_encode($permission);
        return $model->update();
    }

    public static function isValidAuth($auth)
    {
        if (($auth < 0) || ($auth > 4)) {
            return false;
        }
        return true;
    }

    public function isValid(array $route) 
    {
        if (isset($route['pattern']) == false) return false;
        if (isset($route['handler_class']) == false) return false;
        if (isset($route['handler_method']) == false) return false;
        if (trim($route['type']) == "") return false;
        if (trim($route['method']) == "") return false;
        if (Self::isValidAuth($route['auth']) == false) return false;
        return true;
    }
}
