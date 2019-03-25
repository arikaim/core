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
use Arikaim\Core\Access\Access;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Traits\Db\Find;;
use Arikaim\Core\Traits\Db\Status;;
use Arikaim\Core\Traits\Db\Uuid;;

/**
 * Routes database model
 */
class Routes extends Model  
{
    use Uuid,
        Find,
        Status;

    /**
     *  Route types
     */
    const TYPE_UNKNOW   = 0;
    const TYPE_PAGE     = 1;
    const TYPE_API      = 2;
  
    protected $fillable = [
        'name',
        'pattern',
        'method',
        'handler_class',
        'handler_method',
        'extension_name',
        'auth',
        'type',
        'status',
        'template_name',
        'required_permission',
        'permission_type',
        'template_page'];
        
    public $timestamps = false;

    public function disableExtensionRoutes($extension_name) 
    {  
        $routes = $this->where('extension_name','=',$extension_name)->get();
        foreach ($routes as $route) {
            $route->status = Self::DISABLED();
            // fire event        
            Arikaim::event()->trigger('core.route.disable',$route->toArray());
            $route->update();
        }
    }

    public function enableExtensionRoutes($extension_name) 
    {  
        $routes = $this->where('extension_name','=',$extension_name);
        $result = $routes->update(['status' => Self::ACTIVE()]);
        return $result;
    }

    public function getRoutes($status = 1, $extension_name = null)
    {
        if (Schema::hasTable($this) == false) {
            return [];
        } 
        $model = $this;
        if ($status != null) {
            $model = $this->where('status','=',$status);
        }
        if ($extension_name != null) {
            $model = $model->where('extension_name','=',$extension_name);
        }
        $model = $model->get();

        return (is_object($model) == true) ? $model->toArray() : [];
    }

    public function deleteExtensionRoutes($extension_name)
    {
        $model = $this->where('extension_name','=',$extension_name);
        return (is_object($model) == true) ? $model->delete() : true;        
    }

    /**
     * Remove template routes
     *
     * @param string $template_name Use * for all templates
     * @return bool
     */
    public function deleteTemplateRoutes($template_name)
    {
        if ($template_name == '*') {
            $model = $this->whereNotNull('template_name')->where('type','=',Self::TYPE_PAGE);
        } else {
            $model = $this->where('template_name','=',$template_name);
        }
      
        if (is_object($model) == true) {
            $result = $model->delete();
            return ($result == null) ? true : $result;
        }
        return true;
    }

    public function deleteRoute($method, $pattern)
    {       
        $model = $this->where('method','=',$method)->where('pattern','=',$pattern);
        if (is_object($model) == true) {
            $result = $model->delete();
            return ($result == null) ? true : $result;
        }
        return true;
    }

    public function getRoute($method, $pattern)
    {
        $model = $this->where('method','=',$method)->where('pattern','=',$pattern)->first();
        if (is_object($model) == false) {
            return false;
        }
        return $model;
    }

    public function getTemplateRoute($pattern, $template_name)
    {
        $pattern .= $this->getLanguagePattern($pattern);       
        $model = $this->where('pattern','=',$pattern);
        if (is_object($model) == false) {
            return false;
        }
        $model = $model->where('template_name','=',$template_name)->first();
        return (is_object($model) == false) ? false : $model;           
    }

    public function getPageRoute($method, $pattern)
    {
        $pattern .= $this->getLanguagePattern($pattern);
        return $this->getRoute($method,$pattern);       
    }

    public function findRoute($condition)
    {
        $model = Model::buildQuery($this,$condition);
        $model = $model->get();
        return (is_object($nodel) == false) ? false : $model;          
    }

    public function hasRoute($method, $pattern)
    {
        $model = $this->getRoute($method, $pattern);
        return ($model == false) ? false : true; 
    }

    public function addRoute(array $route)
    {
        $result = false;
        if ($this->hasRoute($route['method'],$route['pattern']) == false) {
            $result = Routes::create($route); 
        }       
        return $result;
    }

    public function addTemplateRoute($pattern, $handler_class, $handler_method, $template_name, $template_page)
    {
        $route['method'] = "GET";
        $route['pattern'] = $pattern . $this->getLanguagePattern($pattern);
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
        $route['pattern'] = $pattern . $this->getLanguagePattern($pattern);
        $route['handler_class'] = $handler_class;
        $route['handler_method'] = $handler_method;
        $route['auth'] = $auth;
        $route['type'] = Self::TYPE_PAGE;
        $route['extension_name'] = $extension_name;
        return $this->addRoute($route);
    }

    public function getLanguagePattern($path)
    {        
        return (substr($path,-1) == "/") ? "[{language:[a-z]{2}}/]" : "[/{language:[a-z]{2}}/]";
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
        $model = $this->findById($id);
        if (is_object($model) == false) {
            return false;
        }
    
        $model->permission = $permission_name;
        $model->permission_type = json_encode($permission);
        return $model->update();
    }

    public static function isValidAuth($auth)
    {
        return (($auth < 0) || ($auth > 4)) ? false : true;           
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
