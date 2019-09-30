<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System\Error;

use Arikaim\Core\System\Error\PhpError;

/**
 * Application error handler
 */
class ApplicationError extends PhpError
{  
    /**
     * Invoke error handler
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param \Exception             $exception  
     *
     * @return ResponseInterface    
     */
    public function __invoke($request, $response, $exception)
    {
        return $this->renderError($request,$response,$exception);
    }
}
