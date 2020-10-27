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
        $headers = $this->getParam('headers',[]);
        $cacheControl = $headers['CacheControl'] ?? 'max-age=3600,public';

        $response = $handler->handle($request);
    
        // set cache control header
        return $response->withHeader('Cache-Control',$this->getParam('CacheControl',$cacheControl));      
    }    
}
