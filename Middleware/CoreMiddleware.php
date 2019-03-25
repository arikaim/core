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

use Arikaim\Core\Validator\Validator;
use Arikaim\Core\Middleware\ClientIp;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;

class CoreMiddleware
{
    public function __invoke($request, $response, $next)
    {
        // set current path 
        Arikaim::session()->set('current.path',$request->getUri()->getPath());
        
        // sanitize requets body
        $request = $this->sanitizeRequest($request);
        
        // get client ip address
        $cleint_ip = new ClientIp();
        $request = $cleint_ip->getClientIpAddress($request);
        
        // auth token and session 
        $result = Arikaim::access()->fetchToken($request);
        if ($result == false) {
            if (Model::Users()->isLoged() == true) {
                // create token for current user
                $user = Model::Users()->getLogedUser();
                if ($user != false) {
                    $token = Arikaim::access()->createToken($user->id,$user->uuid);  
                    Arikaim::access()->applyToken($token);  
                }
            }
        }

        $response = $next($request, $response);
        return $response;
    }

    private function sanitizeRequest($request)
    {
        $data = $request->getParsedBody();
        $data = (is_array($data) == true) ? $data : []; 
        $validator = new Validator($data);
        $validator->addFilter('*',$validator->filter()->text());
        $validator->doFilter();
        return $request->withParsedBody($validator->toArray());
    }
}
