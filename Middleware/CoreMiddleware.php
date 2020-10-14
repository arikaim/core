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
        Session::set('current.path',$request->getUri()->getPath());
        
        // sanitize requets body  
        $data = $request->getParsedBody();
        $data = (\is_array($data) == true) ? $data : [];    
        $data = $this->sanitizeArray($data);
      
        $request->withParsedBody($data);

        // get client ip address      
        $Ip = ClientIp::getClientIpAddress($request);
        $request->withAttribute('client_ip',$Ip);   
        Session::set('client_ip',$Ip);
     
        $response = $handler->handle($request);
        
        // set cache control header
        return $response->withHeader('Cache-Control',$this->getParam('CacheControl','max-age=3600,public'));      
    }

    /**
     * Sanitize array 
     *
     * @param array $data
     * @return array
     */
    private function sanitizeArray(array $data) 
    {
        foreach ($data as $key => $value) {
            $data[$key] = (\is_array($value) == true) ? $this->sanitizeArray($value) : \trim($value);
        }

        return $data;
    }
}
