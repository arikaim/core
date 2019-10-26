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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Middleware\Middleware;

/**
 * Token auth middleware
 */
class TokenAuthentication extends Middleware implements MiddlewareInterface
{
    /**
     * Process middleware
     * 
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
    */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {      
        $token = $this->readToken($request);
        $result = Arikaim::auth()->withProvider('token')->authenticate(['token' => $token]);
        if ($result === false) {          
            if (empty(Arikaim::auth()->withProvider('session')->getId()) == true) {
                return $this->resolveAuthError($request);
            }           
        }

        return $handler->handle($request);  
    }

    /**
     * Get token from request header or cookies
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function readToken(ServerRequestInterface $request)
    {   
        $route = $request->getAttribute('route');
        $token = $route->getArgument('token'); 
      
        if (empty($token) == true) {
            // try from cokies TODO
            // $token = Arikaim::cookies()->get('token');
        }
        if (empty($token) == true) {
            // try from requets body 
            $vars = $request->getParsedBody();
            $token = (isset($vars['token']) == true) ? $vars['token'] : null;             
        }       
        return $token;
    }
}
