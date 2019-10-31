<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Error;

use Arikaim\Core\System\Error\PhpError;

/**
 * Application php error handler
 */
class ApplicationPHPError extends PhpError
{
    /**
     * Invoke error handler
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param \Throwable             $error     The caught Throwable object
     *
     * @return ResponseInterface    
     */
    public function __invoke($request, $response, $error)
    {
        return $this->renderError($request,$response,$error);
    }
}
