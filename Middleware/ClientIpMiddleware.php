<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Arikaim\Core\Utils\ClientIp;
use Arikaim\Core\Middleware\Middleware;
use Arikaim\Core\Http\Session;

/**
 * Cient Ip middleware
 */
class ClientIpMiddleware extends Middleware implements MiddlewareInterface
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
        // get client ip address      
        $ip = ClientIp::getClientIpAddress($request);
        $request = $request->withAttribute('client_ip',$ip);   
        Session::set('client_ip',$ip);
        
        return $handler->handle($request);             
    }    
}
