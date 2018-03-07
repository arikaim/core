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

use Arikaim\Core\Arikaim;
use Arikaim\Core\Api\ApiResponse;

class JwtAuthentication
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
        if ((Arikaim::access()->isJwtAuth() === false) || (Arikaim::access()->isValidUser() == false)) { 
            Arikaim::logger()->alert(Arikaim::getError("AUTH_FAILED"),['token' => 'Not valid JWT token']);   
            $response = new ApiResponse($response);
            return $response->displayAuthError();
        }
        return $next($request, $response);
    }
}
