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

use Arikaim\Core\Routes\RoutesStorageInterface;

use Arikaim\Core\Db\Traits\Find;
use Arikaim\Core\Db\Traits\Status;
use Arikaim\Core\Db\Traits\Uuid;

/**
 * Routes database model
 */
class Routes extends Model implements RoutesStorageInterface
{
    use Uuid,
        Find,
        Status;

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
        'redirect_url',
        'auth',
        'type',
        'status',
        'template_name',      
        'options',  
        'regex',
        'middlewares',
        'page_name'
    ];
    
    /**
     * Attribute casting
     *
     * @var array
     */
    protected $casts = [      
        'middlewares' => 'array'
    ];

    /**
     * Visible attributes
     *
     * @var array
     */
    protected $visible = [
        'uuid',
        'name',
        'pattern',
        'method',
        'handler_class',
        'handler_method',
        'extension_name',
        'redirect_url',
        'auth',
        'type',
        'status',
        'template_name',      
        'options',  
        'regex',
        'middlewares',
        'page_name'      
    ];

    /**
     * Db table name
     *
     * @var string
     */
    protected $table = 'routes';

    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Mutator (get) for middlewares attribute.
     *
     * @return array
     */
    public function getMiddlewareAttribute()
    {           
        return (empty($this->attributes['middlewares']) == true) ? [] : \json_decode($this->attributes['middlewares'],true);
    }

    /**
     * Add route middleware
     *
     * @param string $method
     * @param string $pattern
     * @param string $middlewareClass
     * @return bool
     */
    public function addMiddleware(string $method, string $pattern, string $middlewareClass): bool
    {
        $model = $this->where('method','=',$method)->where('pattern','=',$pattern)->first();
        if (\is_null($model) == true) {
            return false;
        }
        $middlewares = $model->middleware;
        $middlewares[] = $middlewareClass;
        $middlewares = \array_unique($middlewares);
        
        return (bool)$model->update([
            'middlewares' => \json_encode($middlewares)
        ]);
    }

    /**
     * Get route details
     *
     * @param string|int $id  Route id or uuid
     * @return array|null
     */
    public function getRouteDetails($id): ?array
    {
        $model = $this->findById($id);

        return ($model === false) ? null : $model->toArray();
    }

    /**
     * Get home page route
     *
     * @return array
     */
    public function getHomePageRoute(): array
    {
        $model = $this->where('status','=',1)->where('type','=',3)->first();

        return (empty($model) == false) ? [$model->toArray()] : [];
    }

    /**
     * Get routes list for request method
     *
     * @param string $method
     * @param int|null $type
     * @return array
     */
    public function searchRoutes(string $method, ?int $type = null): array
    {
        $model = $this->where('status','=',1);
        if (empty($type) == false) {
            $model = $model->where('type','=',$type);
        }
        $model = $model->where('method','like','%' . $method . '%')->orderByDesc('type')->get();
      
        return (\is_object($model) == true) ? $model->toArray() : [];
    }

    /**
     * Mutator (set) for options attribute.
     *
     * @param array|null $value
     * @return void
     */
    public function setOptionsAttribute($value)
    {
        $value = (\is_array($value) == true) ? $value : [];    
        $this->attributes['options'] = \json_encode($value);
    }

    /**
     * Get routes
     *
     * @param array $filter  
     * @return array
     */
    public function getRoutes(array $filter = []): array
    {
        $model = $this;
        foreach ($filter as $key => $value) {
            $model = ($value == '*') ? $model->whereNotNull($key) : $model->where($key,'=',$value);      
        }
        $model = $model->get();

        return (\is_object($model) == true) ? $model->toArray() : [];
    }

    /**
     * Delete routes
     *
     * @param array $filterfilter
     * @return boolean
     */
    public function deleteRoutes(array $filter = []): bool
    {
        $model = $this;

        foreach ($filter as $key => $value) {
            $model = ($value == '*') ? $model->whereNotNull($key) : $model->where($key,'=',$value);                          
        }
        $result = $model->delete();

        return ($result !== false);
    }

    /**
     * Set routes status
     *
     * @param array     $filterfilter
     * @param integer   $status
     * @return boolean
     */
    public function setRoutesStatus(array $filter = [], int $status): bool
    {
        $model = $this;
        foreach ($filter as $key => $value) {
            $model = $model->where($key,'=',$value);
        }

        return (\is_object($model) == true) ? (bool)$model->update(['status' => $status]) : false;
    }

    /**
     * Delete route
     *
     * @param string $method
     * @param string $pattern
     * @return bool
     */
    public function deleteRoute(string $method, string $pattern): bool
    {       
        $model = $this->where('method','=',$method)->where('pattern','=',$pattern);
        if (\is_object($model) == true) {
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
     * @return array|false
     */
    public function getRoute(string $method, string $pattern)
    {
        $model = $this->where('method','=',$method)->where('pattern','=',$pattern)->first();

        return (\is_object($model) == false) ? false : $model->toArray();          
    }

    /**
     * Save route redirect url
     *
     * @param string $method
     * @param string $pattern
     * @param string $url
     * @return boolean
     */
    public function saveRedirectUrl(string $method, string $pattern, string $url): bool
    {
        $model = $this->where('method','=',$method)->where('pattern','=',$pattern)->first();
        if (empty($model) == false) {
            $model->redirect_url = $url;
            
            return (bool)$model->save();
        }

        return false;
    }

    /**
     * Save route options
     *
     * @param string $method
     * @param string $pattern
     * @param array $options
     * @return boolean
     */
    public function saveRouteOptions(string $method, string $pattern, array $options): bool
    {
        $model = $this->where('method','=',$method)->where('pattern','=',$pattern)->first();
        if (\is_object($model) == true) {
            $model->options = $options; 
            
            return (bool)$model->save();
        }

        return false;
    }

    /**
     * Return true if reoute exists
     *
     * @param string $method
     * @param string $pattern
     * @return boolean
     */
    public function hasRoute(string $method, string $pattern): bool
    {
        $model = $this->getRoute($method, $pattern);

        return ($model !== false);
    }

    /**
     * Add route
     *
     * @param array $route
     * @return bool
     */
    public function addRoute(array $route): bool
    {
        if ($this->hasRoute($route['method'],$route['pattern']) == false) {
            $model = $this->create($route);
            return \is_object($model);
        }  
        $model = $this->where('method','=',$route['method'])->where('pattern','=',$route['pattern'])->first();
        $result = $model->update($route);  
        
        return ($result !== false);
    }

    /**
     * Return true if route info is valid
     *
     * @param array $routeInfo
     * @return boolean
     */
    public function isValid(array $routeInfo): bool 
    {
        return (
            isset($routeInfo['pattern']) == false
            || isset($routeInfo['handler_class']) == false
            || isset($routeInfo['handler_method']) == false 
            || empty(\trim($routeInfo['type'])) == true
            || empty(\trim($routeInfo['method'])) == true
        ) ? false : true;          
    }
}
