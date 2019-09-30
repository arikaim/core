<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Middleware;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Middleware\Middleware;

/**
 * Token auth middleware
 */
class TokenAuthentication extends Middleware
{
    /**
     * Call the middleware
     *
     * @param $request
     * @param $response
     * @param callable $next
     * @return \Psr\Http\Message\ResponseInterface
    */
    public function __invoke($request, $response, $next)
    {      
        $token = $this->readToken($request);
        $result = Arikaim::auth()->withProvider('token')->authenticate(['token' => $token]);
        if ($result === false) {          
            if (empty(Arikaim::auth()->withProvider('session')->getId()) == true) {
                return $this->resolveAuthError($request,$response);
            }           
        }
        return $next($request, $response);         
    }

    /**
     * Get token from request header or cookies
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @return 
     */
    protected function readToken($request)
    {   
        $route = $request->getAttribute('route');
        $token = $route->getArgument('token'); 
      
        if (empty($token) == true) {
            // try from cokies
            $token = Arikaim::cookies()->get('token');
        }
        if (empty($token) == true) {
            // try from requets body 
            $vars = $request->getParsedBody();
            $token = (isset($vars['token']) == true) ? $vars['token'] : null;             
        }       
        return $token;
    }
}
