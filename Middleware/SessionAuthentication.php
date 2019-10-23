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
 * Session auth middleware
 */
class SessionAuthentication extends Middleware
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
        if (empty(Arikaim::auth()->getId()) == true) {
            return $this->resolveAuthError($request);            
        }      

        return $handler->handle($request); 
    }
}
