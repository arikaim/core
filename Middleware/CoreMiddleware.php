<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Arikaim\Core\Validator\Validator;
use Arikaim\Core\Utils\ClientIp;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Middleware\Middleware;

/**
 * Core middleware
 */
class CoreMiddleware extends Middleware implements MiddlewareInterface
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
        // set current path 
        Arikaim::session()->set('current.path',$request->getUri()->getPath());
        
        // sanitize requets body
        $request = $this->sanitizeRequest($request);
        
        // get client ip address      
        $Ip = ClientIp::getClientIpAddress($request);
        $request->withAttribute('client_ip',$Ip);   
        Arikaim::session()->set('client_ip',$Ip);

        return $handler->handle($request);
    }

    /**
     * Sanitize request 
     *
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
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
