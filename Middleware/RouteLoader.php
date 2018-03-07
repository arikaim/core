<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Middleware;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Extension\Routes;

class RouteLoader 
{
    public function __construct() 
    {               
    }
    
    public function __invoke($request, $response, $next) 
    {
        $route = $request->getAttribute('route');
        if ($route == null) {                
            $new_route = $this->mapRoute($request);
            if ($new_route != null) {               
                return $new_route->run($request, $response);
            }        
        }
        $response = $next($request, $response);
        return $response;
    }   
    
    public function mapRoute($request)
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        $method = $request->getMethod();
    
        $routes = new Routes();
        $route = $routes->getRoute($path,$method);
        $router = Arikaim::router();

        if ($route != false) {                
            $path = $route['path'] . $route['pattern'];
            $methods = explode(',',$route['method']);
            $auth = $route['auth'];

            $middleware = Factory::createAuthMiddleware($auth);
           
            $route =  Arikaim::$app->map($methods,$path,$route['callable']); //$router->map($methods,$path,$route['callable']);
            if (($middleware != null) && ($route != null)) {
                // $route->add($middleware);
            }

            $routeInfo = $router->dispatch($request);
            print_r($routeInfo);
            $routeArguments = [];
            foreach ($routeInfo[2] as $k => $v) {
                $routeArguments[$k] = urldecode($v);
            }

            $route->prepare($request, $routeArguments);
            return $route;           
        }

        return $route;
    }
}
