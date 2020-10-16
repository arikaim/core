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
use Slim\Exception\HttpException;
use Slim\Interfaces\ErrorHandlerInterface;

use Arikaim\Core\App\Install;
use Throwable;
use PDOException;
use RuntimeException;

/**
 * Error middleware class
*/
class ErrorMiddleware implements MiddlewareInterface
{
    /**
     * @var bool
     */
    protected $displayErrorDetails;

    /**
     * @var bool
     */
    protected $logErrors;

    /**
     * @var bool
     */
    protected $logErrorDetails;

    /**
     * @var array
     */
    protected $handlers = [];

    /**
     * @var object|null
     */
    protected $defaultErrorHandler;

    /**
     * Constructor 
     * 
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param bool $logErrorDetails
     */
    public function __construct(bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails) 
    {       
        $this->displayErrorDetails = $displayErrorDetails;
        $this->logErrors = $logErrors;
        $this->logErrorDetails = $logErrorDetails;
    }

    /**
     * Process middleware
     * 
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        }      
        catch (PDOException $e) {
            return $this->handleException($request,$e,$handler);           
        }  
        catch (RuntimeException $e) {          
            return $this->handleException($request,$e,$handler);
        }
        catch (Throwable $e) {           
            return $this->handleException($request,$e,$handler);
        }
    }

    /**
     * Handle 
     * 
     * @param ServerRequestInterface $request
     * @param Throwable              $exception
     * @return ResponseInterface
     */
    public function handleException(ServerRequestInterface $request, Throwable $exception, $handler): ResponseInterface
    {
        if ($exception instanceof HttpException) {
            $request = $exception->getRequest();
        }

        if (Install::isInstalled() == false) {                
            if (Install::isApiInstallRequest() == true) {
                return $handler->handle($request); 
            } elseif (Install::isInstallPage() == false) {                  
                return $this->defaultErrorHandler->getResponse()->withHeader('Location',Install::getInstallPageUrl());                  
            } 
           
            return $handler->handle($request);
        }

        $exceptionType = \get_class($exception);
        $handler = $this->getErrorHandler($exceptionType);

        return $handler($request, $exception, $this->displayErrorDetails, $this->logErrors, $this->logErrorDetails);
    }

    /**
     * Get error handler 
     * 
     * @param string $type Exception/Throwable name. ie: RuntimeException::class
     * @return object
     */
    public function getErrorHandler(string $type)
    {
        if (isset($this->handlers[$type]) == true) {
            return $this->handlers[$type];
        }

        return $this->getDefaultErrorHandler();
    }

    /**
     * Get default error handler
     *
     * @return object
     */
    public function getDefaultErrorHandler()
    {
        return $this->defaultErrorHandler;
    }

    /**
     * Set callable as the default Slim application error handler.
     *
     * @param object $handler
     * @return self
     */
    public function setDefaultErrorHandler($handler): self
    {
        $this->defaultErrorHandler = $handler;

        return $this;
    }

    /**
     * Set callable to handle scenarios where an error
     *
     * @param string $type Exception/Throwable name. ie: RuntimeException::class
     * @param ErrorHandlerInterface $handler
     * @return self
     */
    public function setErrorHandler(string $type, $handler): self
    {
        $this->handlers[$type] = $handler;
        return $this;
    }
}
