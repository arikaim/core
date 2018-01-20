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

use Arikaim\Core\Form\Form;
use Arikaim\Core\Middleware\ClientIp;

class CoreMiddleware 
{
    public function __construct() 
    {        
       
    }

    public function __invoke($request, $response, $next) 
    {         
        // sanitize requets body              
        $request = $this->sanitizeRequest($request);
        
        // get client ip address
        $cleint_ip = new ClientIp();
        $request = $cleint_ip->getClientIpAddress($request);
        
        $response = $next($request, $response);
        return $response;   
    }

    private function sanitizeRequest($request)
    {
        $form = new Form($request->getParsedBody());
        $form->addFilter('*',Form::Filter()->text());        
        $form->sanitize();
        return $request->withParsedBody($form->toArray());      
    }
}
