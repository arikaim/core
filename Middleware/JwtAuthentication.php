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
 * JWT auth middleware
 */
class JwtAuthentication extends Middleware implements MiddlewareInterface
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

        if ($token === false) {
            return $this->resolveAuthError($request);
        } 

        if (Arikaim::auth()->withProvider('jwt')->authenticate(['token' => $token]) == false) {
            return $this->resolveAuthError($request);
        };
        
        return $handler->handle($request);
    }

    /**
     * Get token from request header
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @return string|false Base64 encoded JSON Web Token, Session ID or false if not found.
     */
    protected function readToken(ServerRequestInterface $request)
    {   
        $headers = $request->getHeader('Authorization');
        $header = isset($headers[0]) ? $headers[0] : "";
    
        if (empty($header) && function_exists("apache_request_headers")) {
            $headers = apache_request_headers();
            $header = isset($headers['Authorization']) ? $headers['Authorization'] : "";
        }

        return (preg_match('/Bearer\s+(.*)$/i', $header, $matches) == true) ? $matches[1] : false;
    }
}
