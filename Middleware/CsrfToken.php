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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Access\Csrf;
use Arikaim\Core\Api\Response;
use Arikaim\Core\Utils\Request;
use Arikaim\Core\Middleware\Middleware;

/**
 * Verify Csrf token middleware
 */
class CsrfToken extends Middleware implements MiddlewareInterface
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
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    protected function generateToken(ServerRequestInterface $request)
    {
        if ($this->getParam('recreate_token') == true) {
            $token = Csrf::createToken();
            $request = $request->withAttribute('csrf_token', $token);
        }    

        return $request;
    }

    /**
     * Show token error // TODO
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return void
     */
    protected function displayTokenError(ServerRequestInterface $request, $response = null)
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
     * @param ServerRequestInterface $request
     * @return string|null
     */
    public function getToken(ResponseInterface $request)
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
