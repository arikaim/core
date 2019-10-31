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

use Arikaim\Core\Arikaim;
use Arikaim\Core\Middleware\Middleware;

/**
 * Basic HTTP auth middleware
 */
class BasicAuthentication extends Middleware implements MiddlewareInterface
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
        if (empty(Arikaim::auth()->getId()) == false) {
            return $handler->handle($request);
        }
        // auth
        $credentials = $this->getCredentials($request);
        if (Arikaim::auth()->withProvider('basic')->authenticate($credentials) == false) {
            return $this->resolveAuthError($request);
        }

        return $handler->handle($request);
    }

    /**
     * Get basic http auth credentials
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @return array
     */
    protected function getCredentials($request)
    {
        $credentials = [
            'user_name' => $request->headers()->get('PHP_AUTH_USER'),
            'password'  => $request->headers()->get('PHP_AUTH_PW')
        ];

        return $credentials;
    }
}
