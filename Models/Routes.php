<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Models;

use Illuminate\Database\Eloquent\Model;

use Arikaim\Core\Db\Schema;
use Arikaim\Core\Access\Authenticate;
use Arikaim\Core\App\Url;
use Arikaim\Core\System\Routes as SystemRoutes;
use Arikaim\Core\View\Html\HtmlComponent;
use Arikaim\Core\Traits\Db\Find;
use Arikaim\Core\Traits\Db\Status;
use Arikaim\Core\Traits\Db\Uuid;

/**
 * Routes database model
 */
class Routes extends Model  
{
    use Uuid,
        Find,
        Status;

    /**
     *  Route type constant
     */
    const TYPE_UNKNOW          = 0;
    const TYPE_PAGE            = 1;
    const TYPE_API             = 2;
    const TYPE_ERROR_PAGE      = 3;
    const TYPE_AUTH_ERROR_PAGE = 4;

    /**
     * Fillable attributes
     *
     * @var array
    */
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
        'options',  
        'redirect_url',
        'page_name'
    ];
    
    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Get full redirect url
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return (empty($this->redirect_url) == false) ? Url::ARIKAIM_BASE_URL . $this->redirect_url : null;
    }
    
    /**
     * Get route url (parse route pattern with data)
     *
     * @param array $data
     * @param array $queryParams
     * @return string
     */
    public function getRouteUrl(array $data = [], array $queryParams = [])
    {
        return ($this->hasPlaceholder() == false) ? $this->pattern : SystemRoutes::getRouteUrl($this->pattern,$data,$queryParams);
    }

    /**
     * Mutator (set) for options attribute.
     *
     * @param array:null $value
     * @return void
     */
    public function setOptionsAttribute($value)
    {
        $value = (is_array($value) == true) ? $value : [];    
        $this->attributes['options'] = json_encode($value);
    }

    /**
     * Mutator (get) for options attribute.
     *
     * @return array
     */
    public function getOptionsAttribute()
    {
        return json_decode($this->attributes['options'],true);
    }

    /**
     * Get extension routes
     *
     * @param string $extension
     * @param integer $status
     * @param integer $type
     * @return object|null
     */
    public function getExtensionRoutes($extension, $type = Self::TYPE_PAGE, $status = 1)
    {
        $routes = $this->where('extension_name','=',$extension);
        if ($type != null) {
            $routes = $routes->where('type','=', $type);
        }
        if ($status != null) {
            $routes = $routes->where('status','=', $status);
        }         
        return $routes->get();
    }

    /**
     * Get page routes query
     *
     * @param string $extension
     * @param integer $status
     * @return QueryBuilder
     */
    public function getPageRoutesQuery($extension = null, $status = null)
    {
        $query = $this->where('type','=',Self::TYPE_PAGE);
        
        if ($extension != null) {
            $query = $query->where('extension_name','=',$extension);
        }
        if ($status != null) {
            $query = $query->where('status','=', $status);
        }  

        return $query;
    }

    /**
     * Return true if route pattern have placeholder
     *
     * @return boolean
     */
    public function hasPlaceholder()
    {
        return SystemRoutes::hasPlaceholder($this->pattern);
    }

    /**
     * Disable extension routes
     *
     * @param string $extension
     * @return bool
     */
    public function disableExtensionRoutes($extension) 
    {  
        $routes = $this->where('extension_name','=',$extension);
        $result = $routes->update(['status' => Status::$DISABLED]);
        return ($result == null) ? true : $result;
    }

    /**
     * Enable extension routes
     *
     * @param string $extension
     * @return bool
     */
    public function enableExtensionRoutes($extension) 
    {  
        $routes = $this->where('extension_name','=',$extension);
        $result = $routes->update(['status' => Status::$ACTIVE]);
        return ($result == null) ? true : $result;
    }

    /**
     * Get routes
     *
     * @param integer $status
     * @param string|null $extension
     * @return array
     */
    public function getRoutes($status = 1, $extension = null)
    {
        if (Schema::hasTable($this) == false) {
            return [];
        } 
        $model = $this;
        if ($status != null) {
            $model = $this->where('status','=',$status);
        }
        if ($extension != null) {
            $model = $model->where('extension_name','=',$extension);
        }
        $model = $model->get();

        return (is_object($model) == true) ? $model->toArray() : [];
    }

    /**
     * Delete extension routes
     *
     * @param string $extension
     * @return bool
     */
    public function deleteExtensionRoutes($extension)
    {
        $model = $this->where('extension_name','=',$extension);
        if (is_object($model) == true) {
            $result = $model->delete();
        }
        return ($result == null) ? true : $result;
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

    /**
     * Delete route
     *
     * @param string $method
     * @param string $pattern
     * @return bool
     */
    public function deleteRoute($method, $pattern)
    {       
        $model = $this->where('method','=',$method)->where('pattern','=',$pattern);
        if (is_object($model) == true) {
            $result = $model->delete();
            return ($result == null) ? true : $result;
        }
        return true;
    }

    /**
     * Get route
     *
     * @param string $method
     * @param string $pattern
     * @return Model|false
     */
    public function getRoute($method, $pattern)
    {
        $model = $this->where('method','=',$method)->where('pattern','=',$pattern)->first();
        return (is_object($model) == false) ? false : $model;          
    }

    /**
     * Get route with language pattern
     *
     * @param string $method
     * @param string $pattern
     * @return Model|false
     */
    public function getPageRoute($method, $pattern)
    {
        $pattern .= $this->getLanguagePattern($pattern);         
        return $this->getRoute($method,$pattern);       
    }

    /**
     * Return true if reoute exists
     *
     * @param string $method
     * @param string $pattern
     * @return boolean
     */
    public function hasRoute($method, $pattern)
    {
        $model = $this->getRoute($method, $pattern);
        return ($model == false) ? false : true; 
    }

    /**
     * Add route
     *
     * @param array $route
     * @return bool
     */
    public function addRoute(array $route)
    {
        return ($this->hasRoute($route['method'],$route['pattern']) == false) ? $this->create($route) : $this->update($route);         
    }

    /**
     * Set redirect url for route
     *
     * @param string $pattern
     * @param string $redirect_url
     * @return boolean
     */
    public function setRedirectUrl($pattern, $redirect_url)
    {
        $model = $this->getRoute('GET',$pattern);
        if (is_object($model) == true) {
            $model->redirect_url = $redirect_url;
            return $model->save();
        }
        return false;
    }

    /**
     * Add template(theme) route
     *
     * @param string $pattern
     * @param string $handler_class
     * @param string $handler_method
     * @param string $template_name
     * @param string $page_name
     * @param integer $auth
     * @return bool
     */
    public function addTemplateRoute($pattern, $handler_class, $handler_method, $template_name, $page_name, $auth = null)
    {
        $route = [
            'method'         => "GET",
            'pattern'        => $pattern . $this->getLanguagePattern($pattern),
            'handler_class'  => $handler_class,
            'handler_method' => $handler_method,
            'auth'           => $auth,
            'type'           => Self::TYPE_PAGE,
            'page_name'  => $page_name,
            'extension_name' => null,
            'template_name'  => $template_name
        ];
        $model = $this->getPageRoute('GET',$pattern);
       
        return (is_object($model) == true) ? $model->update($route) : $this->create($route);      
    }

    /**
     * Add page route
     *
     * @param string $pattern
     * @param string $handler_class
     * @param string $handler_method
     * @param string $extension
     * @param string $page_name
     * @param integer $auth  
     * @param integer $type
     * @param string|null $redirect_url
     * @param string|null $route_name
     * @param boolean $with_language
     * @return bool
     */
    public function addPageRoute($pattern, $handler_class, $handler_method, $extension, $page_name, $auth = null, $redirect_url = null, $route_name = null, $with_language = true)
    {
        $language_pattern = ($with_language == true) ? $this->getLanguagePattern($pattern) : '';
        $route = [
            'method'            => "GET",
            'pattern'           => $pattern . $language_pattern,
            'handler_class'     => $handler_class,
            'handler_method'    => $handler_method,
            'auth'              => $auth,
            'type'              => Self::TYPE_PAGE,
            'extension_name'    => $extension,
            'page_name'         => $page_name,
            'template_name'     => null,
            'name'              => $route_name,
            'redirect_url'      => $redirect_url
        ];

        $model = $this->getPageRoute('GET',$pattern);
       
        return (is_object($model) == true) ? $model->update($route) : $this->create($route);      
    }

    /**
     * Add application error page route
     *
     * @param string $pattern
     * @param string $handler_class
     * @param string $handler_method
     * @param string $extension
     * @param string $page_name
     *
     * @return bool
     */
    public function addErrorRoute($pattern, $handler_class, $handler_method, $extension, $page_name)
    {
        return $this->addPageRoute($pattern,$handler_class,$handler_method,$extension,$page_name,null,Self::TYPE_ERROR_PAGE);
    }

    /**
     * Add auth error page route
     *
     * @param string $pattern
     * @param string $handler_class
     * @param string $handler_method
     * @param string $extension
     * @param integer|null $auth
     * @param string $page_name
     *
     * @return bool
    */
    public function addAuthErrorRoute($pattern, $extension, $page_name, $auth = null, $handler_class, $handler_method)
    {
        return $this->addPageRoute($pattern,$handler_class,$handler_method,$extension,$page_name,$auth,Self::TYPE_AUTH_ERROR_PAGE);
    }

    /**
     * Get error route
     *
     * @param string $extension
     * @return Model|false
     */
    public function getErrorRoute($extension)
    {
        $model = $this->where('method','=','GET')->where('type','=',Self::TYPE_ERROR_PAGE)->where('extension_name','=',$extension)->first();
        return (is_object($model) == true) ? $model : false;
    }

    /**
     * Get auth error route
     *
     * @param string $extension
     * @param integer $auth
     * @return void
     */
    public function getAuthErrorRoute($extension, $auth)
    {
        $model = $this->where('method','=','GET')
            ->where('type','=',Self::TYPE_AUTH_ERROR_PAGE)
            ->where('extension_name','=',$extension)
            ->where('auth','=',$auth)
            ->first();

        return (is_object($model) == true) ? $model : false;
    }

    /**
     * Get language route path  
     *
     * @param string $path
     * @return string
     */
    public function getLanguagePattern($path)
    {        
        return (substr($path,-1) == "/") ? "[{language:[a-z]{2}}/]" : "[/{language:[a-z]{2}}/]";
    }

    /**
     * Add api route
     *
     * @param string $method
     * @param string $pattern
     * @param string $handler_class
     * @param string $handler_method
     * @param string $extension
     * @param integer $auth
     * @return bool
     */
    public function addApiRoute($method, $pattern, $handler_class, $handler_method, $extension, $auth = Authenticate::AUTH_JWT)
    {
        $route = [
            'method'         => $method,
            'pattern'        => $pattern,
            'handler_class'  => $handler_class,
            'handler_method' => $handler_method,
            'auth'           => $auth,
            'type'           => Self::TYPE_API,
            'extension_name' => $extension
        ];
        return $this->addRoute($route);
    }
    
    /**
     * Return true if auth id is valid
     *
     * @param integer $auth
     * @return boolean
     */
    public static function isValidAuth($auth)
    {
        return ($auth < 0 || $auth > 4) ? false : true;           
    }

    /**
     * Return true if route info is valid
     *
     * @param array $route
     * @return boolean
     */
    public function isValid(array $route) 
    {
        return (
            isset($route['pattern']) == false
            || isset($route['handler_class']) == false
            || isset($route['handler_method']) == false 
            || empty(trim($route['type'])) == true
            || empty(trim($route['method'])) == true
            || Self::isValidAuth($route['auth']) == false
        ) ? false : true;          
    }

    /**
     * Get html full page name
     *
     * @return string
     */
    public function getPageName()
    {
        $page_name = trim($this->page_name);
        return (HtmlComponent::isFullName($page_name) == true) ? $page_name : $this->extension_name . '::' . $page_name;       
    }
}
