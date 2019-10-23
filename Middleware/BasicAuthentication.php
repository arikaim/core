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

use Arikaim\Core\Arikaim;
use Arikaim\Core\Middleware\Middleware;

/**
 * Basic HTTP auth middleware
 */
class BasicAuthentication extends Middleware
{
    /**
     * Call the middleware
     *
     * @param $request
     * @param $handler
     * @return \Psr\Http\Message\ResponseInterface
    */
    public function __invoke($request, $handler)
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
