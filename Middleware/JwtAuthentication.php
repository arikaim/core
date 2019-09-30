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
 * JWT auth middleware
 */
class JwtAuthentication extends Middleware
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

        if ($token === false) {
            return $this->resolveAuthError($request,$response);
        } 

        if (Arikaim::auth()->withProvider('jwt')->authenticate(['token' => $token]) == false) {
            return $this->resolveAuthError($request,$response);
        };
        
        return $next($request, $response);
    }

    /**
     * Get token from request header
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @return string|false Base64 encoded JSON Web Token, Session ID or false if not found.
     */
    protected function readToken($request)
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
