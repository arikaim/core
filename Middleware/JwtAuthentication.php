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

use Arikaim\Core\Middleware\Jwt;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Api\ApiAuthentication;

class JwtAuthentication extends ApiAuthentication
{
    private $key;

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
        $route = $request->getAttribute('route');
        if ($route != null) {
            if ($route->getArgument('name') == 'admin.login') {
                return $next($request, $response);
            }
        }
        $decoded_token = $this->authentication($request); 

        if ($decoded_token === false) { 
            Arikaim::logger()->alert(Arikaim::getError("AUTH_FAILED"),['token' => 'not valid token']);   
            return $this->showError($request,$response);
        }
        $request = $request->withAttribute('jwt_token', $decoded_token);
        return $next($request, $response);
    }

    public function authentication($request)
    {
        $jwt = new Jwt();
        $token = $jwt->getToken($request);
        if ($token === false) {
            return false;
        }
        $decoded_token = $jwt->decodeToken($token);
        if ($decoded_token === false) { 
            return false;
        }
        return $decoded_token;
    }
}
