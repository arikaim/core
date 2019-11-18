<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Error;

use Slim\Interfaces\ErrorHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use Arikaim\Core\System\Error\PhpError;
use Arikaim\Core\Arikaim;

/**
 * Application error handler
 */
class ApplicationError extends PhpError implements ErrorHandlerInterface
{  
    /**
     * Invoke error handler
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param \Exception             $exception  
     * @param bool                   $displayDetails
     * @param bool                   $logErrors
     * @param bool                   $logErrorDetails
     * @return ResponseInterface    
     */
    public function __invoke(ServerRequestInterface $request, Throwable $exception, bool $displayDetails, bool $logErrors, bool $logErrorDetails): ResponseInterface
    {
        return $this->renderError($request,$exception,$displayDetails,$logErrors,$logErrorDetails);
    }

    /**
     * Render error
     *
     * @param \Exception    $exception  
     * @param bool          $displayDetails
     * @param bool          $logErrors
     * @param bool          $logErrorDetails
     * @return string    
     */
    public static function render($exception, $displayDetails = true, $logErrors = false, $logErrorDetails = false)
    {
        $obj = new Self($displayDetails,true);
        $request = Arikaim::createRequest();
    
        return $obj->renderErrorOutput($request,$exception,$displayDetails,$logErrors,$logErrorDetails);
    }
}
