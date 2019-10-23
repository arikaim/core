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
use Arikaim\Core\Access\Csrf;
use Arikaim\Core\Api\Response;
use Arikaim\Core\Utils\Request;
use Arikaim\Core\Middleware\Middleware;

/**
 * Verify Csrf token middleware
 */
class CsrfToken extends Middleware
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
        if (in_array($request->getMethod(),['POST', 'PUT', 'DELETE', 'PATCH']) == true) {
            $token = $this->getToken($request);
            if (Csrf::validateToken($token) == false) {    
                $request = $this->generateToken($request);                                    
                // token error
                return $this->displayTokenError($request);
            }
        }
        $request = $this->generateToken($request);     

        return $handler->handle($request);
    }

    /**
     * Vreate new token if middleware param recreate_token is set to true
     *
     * @param object $request
     * @return object
     */
    protected function generateToken(Request $request)
    {
        if ($this->getParam('recreate_token') == true) {
            $token = Csrf::createToken();
            $request = $request->withAttribute('csrf_token', $token);
        }    

        return $request;
    }

    /**
     * Show token error
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return void
     */
    protected function displayTokenError($request, $response)
    {
        Arikaim::logger()->alert(Arikaim::getError("ACCESS_DENIED"));   
        Arikaim::errors()->addError('ACCESS_DENIED');

        if (Request::acceptJson($request) == true) {
            $response = new Response();
            $response->setError('ACCESS_DENIED');
            return $response->getResponse();
        } 
        
        return Arikaim::page()->load('system:system-error');      
    }

    /**
     * Get csrf token from request
     *
     * @param object $request
     * @return string|null
     */
    public function getToken($request)
    {
        $body = $request->getParsedBody();
        $body = (empty($body) == true) ? [] : $body;
        $token = isset($body['csrf_token']) ? $body['csrf_token'] : null;

        if (empty($token) == true) {          
            $token = $request->getHeaderLine('X-XSRF-TOKEN');
        }

        return $token;
    }    
}
