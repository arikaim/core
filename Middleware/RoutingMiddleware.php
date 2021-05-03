<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Slim\Interfaces\RouteParserInterface;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Routing\RoutingResults;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;

use Arikaim\Core\Interfaces\RoutesInterface;
use Arikaim\Core\App\SystemRoutes;
use Arikaim\Core\Models\Users;
use Arikaim\Core\Models\AccessTokens;
use Arikaim\Core\Http\Url;
use Arikaim\Core\Access\AuthFactory;
use Arikaim\Core\Routes\MiddlewareFactory;
use Arikaim\Core\Routes\RouteType;
use RuntimeException;
use Exception;
use Closure;

/**
 * Routing middleware
 */
class RoutingMiddleware implements MiddlewareInterface
{
    /**
     * @var RouteResolverInterface
     */
    protected $routeResolver;

    /**
     * @var RouteParserInterface
     */
    protected $routeParser;

    /**
     * Route collector
     *
     * @var RouteCollectorInterface
     */
    protected $routeCollector;

    /**
     * Routes storage
     *
     * @var RoutesInterface|null
     */
    protected $routes = null;

    /**
     * @param RouteResolverInterface $routeResolver
     * @param RouteCollectorInterface   $routeCollector
     */
    public function __construct(
        RouteResolverInterface $routeResolver,
        RouteCollectorInterface $routeCollector,
        Closure $routesClosure = null
    )
    {
        $this->routeResolver = $routeResolver;
        $this->routeParser = $routeCollector->getRouteParser();
        $this->routeCollector = $routeCollector;
        $this->routesClosure = $routesClosure;
    }

    /**
     * Resolve routes ref
     *
     * @return void
     */
    protected function resolveRoutes()
    {
        if (\is_callable($this->routesClosure) == true && empty($this->routes) == true) {
            return $this->routes = ($this->routesClosure)();
        }
    }

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     *
     * @throws HttpNotFoundException
     * @throws HttpMethodNotAllowedException
     * @throws RuntimeException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withAttribute('routeParser', $this->routeParser);
        $request = $this->performRouting($request);

        return $handler->handle($request);
    }

    /**
     * Perform routing
     *
     * @param  ServerRequestInterface $request PSR7 Server Request
     * @return ServerRequestInterface
     *
     * @throws HttpNotFoundException
     * @throws HttpMethodNotAllowedException
     * @throws RuntimeException
     */
    public function performRouting(ServerRequestInterface $request): ServerRequestInterface
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $routePath = \rtrim(\str_replace(BASE_PATH,'',$path),'/');
    
        // set current path       
        $type = RouteType::getType($routePath);
     
        switch($type) {
            case RouteType::HOME_PAGE_URL: 
                // home page route                 
                $this->mapRoutes($method,RoutesInterface::HOME_PAGE);
                break;
            case RouteType::ADMIN_PAGE_URL: 
                // map control panel page
                $this->routeCollector->map(['GET'],'/admin[/{language:[a-z]{2}}/]','Arikaim\Core\App\ControlPanel:loadControlPanel');
                break;
            case RouteType::SYSTEM_API_URL: 
                $this->mapSystemRoutes($method);       
                break;
            case RouteType::API_URL: 
                // api routes only 
                $this->mapRoutes($method,RoutesInterface::API);    
                break;
            case RouteType::ADMIN_API_URL: 
                // map amdin pai routes
                $this->mapRoutes($method,RoutesInterface::API);    
                $this->mapRoutes($method,RoutesInterface::ADMIN_API);    
                break;
            case RouteType::UNKNOW_TYPE:                 
                $this->mapRoutes($method,RoutesInterface::PAGE);
                break;            
        }
      
        $routingResults = $this->routeResolver->computeRoutingResults($path,$method);
        $routeStatus = $routingResults->getRouteStatus();

        $request = $request->withAttribute('routingResults',$routingResults);
        
        switch ($routeStatus) {
            case RoutingResults::FOUND:
                $routeArguments = $routingResults->getRouteArguments();
                $routeIdentifier = $routingResults->getRouteIdentifier() ?? '';
                $route = $this->routeResolver->resolveRoute($routeIdentifier)->prepare($routeArguments);
            
                return $request
                            ->withAttribute('route_params',$route->getArguments())
                            ->withAttribute('route',$route)
                            ->withAttribute('current_path',$path);

            case RoutingResults::NOT_FOUND:
                throw new HttpNotFoundException($request);

            case RoutingResults::METHOD_NOT_ALLOWED:
                $exception = new HttpMethodNotAllowedException($request);
                $exception->setAllowedMethods($routingResults->getAllowedMethods());
                throw $exception;

            default:
                throw new RuntimeException('Routing error.');
        }
    }

    /**
     * Map system routes
     *
     * @param string $method
     * @return void
     */
    protected function mapSystemRoutes(string $method): void
    {       
        $routes = SystemRoutes::$routes[$method] ?? false;
        if ($routes === false) {
            return;
        }
      
        if (RouteType::isApiInstallRequest() == false) {
            $user = new Users();
            $middleware = AuthFactory::createMiddleware('session',$user,[]);
        } else {
            // get only install routes
            $routes = SystemRoutes::$installRoutes[$method] ?? false;
        }
       
        foreach ($routes as $item) {          
            $route = $this->routeCollector->map([$method],$item['pattern'],$item['handler']);
            if (empty($item['middleware']) == false) {
                // add middleware 
                $route->add($middleware);
            }      
        }     
    } 

    /**
     * Map extensons and templates routes
     *     
     * @param string $method
     * @param int|null $type
     * @return boolean
     * 
     * @throws Exception
     */
    public function mapRoutes($method, ?int $type = null): bool
    {      
        $this->resolveRoutes();

        try {   
            $routes = [];      
            if (empty($this->routes) == false) {
                $routes = ($type == RoutesInterface::HOME_PAGE) ? [$this->routes->getHomePageRoute()] : $this->routes->searchRoutes($method,$type);                
            }           
            $user = new Users();
            $accessTokens = new AccessTokens();
        } catch(Exception $e) {
            return false;
        }
       
        foreach($routes as $item) {
            $handler = $item['handler_class'] . ':' . $item['handler_method'];  
            $middlewares = $item['middlewares'] ?? [];        
            $middlewares = (\is_string($middlewares) == true) ? \json_decode($item['middlewares'],true) : $middlewares;
            $route = $this->routeCollector->map([$method],$item['pattern'],$handler);

            $routeOptions = \is_null($item['options']) ? '' : $item['options'];
            $pageName = \is_null($item['page_name']) ? '' : $item['page_name'];
            $extensionName = \is_null($item['extension_name']) ? '' : $item['extension_name'];
 
            $route->setArgument('route_options',$routeOptions);
            $route->setArgument('route_page_name',$pageName);
            $route->setArgument('route_extension_name',$extensionName);

            // auth middleware
            if (empty($item['auth']) == false) {
                $options['redirect'] = (empty($item['redirect_url']) == false) ? Url::BASE_URL . $item['redirect_url'] : null;      
                
                $userProvider = ($item['auth'] == 4) ? $accessTokens : $user;     
                $authMiddleware = AuthFactory::createMiddleware($item['auth'],$userProvider,$options);    
            
                if ($authMiddleware != null && \is_object($route) == true) {
                    // add middleware 
                    $route->add($authMiddleware);
                }
            } 
            // add middlewares                        
            foreach ($middlewares as $class) {
                $instance = MiddlewareFactory::create($class);
                if ($instance != null && \is_object($route) == true) {   
                    // add middleware                 
                    $route->add($instance);
                }                   
            }                                                                 
        }    
        
        return true;
    }
}
