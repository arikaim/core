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
use Arikaim\Core\Api\RestApiResponse;
use Arikaim\Core\Middleware\JwtAuthentication;

class SessionAuthentication 
{
    public function __invoke($request, $response, $next) 
    {
        $token = Arikaim::access()->getToken();
       
        if (Arikaim::access()->hasToken() === false) {
            $response = new RestApiResponse($response);
            return $response->displayAuthError();
        }      

        $response = $next($request, $response);
        return $response;        
    }
}
