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

use Slim\Interfaces\ErrorHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use Arikaim\Core\System\Error\PhpError;

/**
 * Application error handler
 */
class ApplicationError extends PhpError implements ErrorHandlerInterface
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
    public function __invoke(ServerRequestInterface $request, Throwable $exception, bool $display_details, bool $log_errors, bool $log_error_details): ResponseInterface
    {
        return $this->renderError($request,$exception, $display_details, $log_errors, $log_error_details);
    }
}
