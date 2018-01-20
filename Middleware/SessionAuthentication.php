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
use Arikaim\Core\Api\ApiAuthentication;
use Arikaim\Core\Middleware\JwtAuthentication;

class SessionAuthentication extends ApiAuthentication 
{
    protected $auth_type;

    public function __invoke($request, $response, $next) 
    {
        $result = $this->authentication($request);       
        if ($result == true) {
            $response = $next($request, $response);
            return $response;  
        }

        // try jwt
        $jwt_auth = new JwtAuthentication();
        $decoded_token = $jwt_auth->authentication($request);
        if ($decoded_token === false) {
            return $this->showError($request, $response);
        }      
        $response = $next($request, $response);
        return $response;        
    }

    private function authentication($request) 
    {
        $request_session_id = Arikaim::cookies()->get('PHPSESSID');
        $session_id = Arikaim::session()->getID();
        if ($request_session_id == $session_id) {           
            return true;
        }  
        return false;
    }
}
