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

use RuntimeException;
use Exception;

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
     * @var RoutesInterface
     */
    protected $routes;

   

    /**
     * @param RouteResolverInterface $routeResolver
     * @param RouteCollectorInterface   $routeCollector
     */
    public function __construct(
        RouteResolverInterface $routeResolver,
        RouteCollectorInterface $routeCollector,
        RoutesInterface $routes
    )
    {
        $this->routeResolver = $routeResolver;
        $this->routeParser = $routeCollector->getRouteParser();
        $this->routeCollector = $routeCollector;
        $this->routes = $routes;
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
   
        if (SystemRoutes::isSystemApiUrl($path) == true) {            
            // map system api routes
            $this->mapSystemRoutes($method,$path);          
        } elseif (SystemRoutes::isAdminPage($path) == false) {            
            // map extensions and template routes                    
            $this->mapRoutes($method,$path);           
        }

        $routingResults = $this->routeResolver->computeRoutingResults($path,$method);
        $routeStatus = $routingResults->getRouteStatus();

        $request = $request->withAttribute('routingResults', $routingResults);
        
        switch ($routeStatus) {
            case RoutingResults::FOUND:
                $routeArguments = $routingResults->getRouteArguments();
                $routeIdentifier = $routingResults->getRouteIdentifier() ?? '';
                $route = $this->routeResolver
                    ->resolveRoute($routeIdentifier)
                    ->prepare($routeArguments);
            
                //  route params
                $pattern = $route->getPattern();
                $routeParams = $this->routes->getRoute('GET',$pattern);
                $request = $request->withAttribute('route_params',$routeParams);

                return $request->withAttribute('route', $route);

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
     * @param string $path
     * @return void
     */
    protected function mapSystemRoutes($method, $path)
    {       
        $routes = SystemRoutes::$routes[$method] ?? false;  
        if ($routes === false) {
            return;
        }
      
        $user = new Users();
        $middleware = AuthFactory::createMiddleware('session',$user,[]);

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
     * @param string $path
     * @return boolean
     * 
     * @throws Exception
     */
    public function mapRoutes($method, $path)
    {      
        try {
            $routes = $this->routes->searchRoutes($method);
            $user = new Users();
        } catch(Exception $e) {
            return false;
        }
       
        foreach($routes as $item) {
            $handler = $item['handler_class'] . ':' . $item['handler_method'];   
            $route = $this->routeCollector->map([$method],$item['pattern'],$handler);
            // auth middleware
            if ($item['auth'] > 0) {
                $options['redirect'] = (empty($item['redirect_url']) == false) ? Url::BASE_URL . $item['redirect_url'] : null;      
                
                $userProvider = ($item['auth'] == 4) ? new AccessTokens() : $user;     
                $middleware = AuthFactory::createMiddleware($item['auth'],$userProvider,$options);

                if ($middleware != null && \is_object($route) == true) {
                    // add middleware 
                    $route->add($middleware);
                }
            }                                                   
        }          
    }
}
