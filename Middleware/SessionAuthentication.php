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

class SessionAuthentication 
{
    public function __invoke($request, $response, $next) 
    {
        $token = Arikaim::access()->getToken();
       
        if (Arikaim::access()->hasToken() === false) {
            Arikaim::logger()->alert(Arikaim::getError("AUTH_FAILED"),['token' => 'Not valid session token']);   
            $response = new ApiResponse($response);
            return $response->displayAuthError();
        }      

        $response = $next($request, $response);
        return $response;        
    }
}
