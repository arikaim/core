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

use Arikaim\Core\Validator\Validator;
use Arikaim\Core\Utils\ClientIp;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Middleware\Middleware;

/**
 * Core middleware
 */
class CoreMiddleware extends Middleware
{
    /**
     * Invoke 
     *
     * @param object $request
     * @param object $handler   
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $handler)
    {
        // set current path 
        Arikaim::session()->set('current.path',$request->getUri()->getPath());
        
        // sanitize requets body
        $request = $this->sanitizeRequest($request);
        
        // get client ip address      
        $client_id = ClientIp::getClientIpAddress($request);
        $request->withAttribute('client_ip',$client_id);   
        Arikaim::session()->set('client_ip',$client_id);

        return $handler->handle($request);
    }

    /**
     * Sanitize request 
     *
     * @param object $request
     * @return object
     */
    private function sanitizeRequest($request)
    {
        $data = $request->getParsedBody();
        $data = (is_array($data) == true) ? $data : []; 
        $validator = new Validator($data);
        $validator->addFilter('*',$validator->filter()->sanitize());
        $validator->doFilter();
        
        return $request->withParsedBody($validator->toArray());
    }
}
