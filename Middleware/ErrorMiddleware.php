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
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpException;

use Arikaim\Core\System\Error\ErrorHandlerInterface;
use Arikaim\Core\System\Error\Renderer\HtmlPageErrorRenderer;
use Arikaim\Core\System\Error\ApplicationError;
use Arikaim\Core\Interfaces\View\HtmlPageInterface;
use Arikaim\Core\App\Install;
use Arikaim\Core\Routes\RouteType;
use Arikaim\Core\Http\Request;
use Throwable;
use PDOException;
use RuntimeException;
use Closure;

/**
 * Error middleware class
*/
class ErrorMiddleware implements MiddlewareInterface
{
    /**
     * @var bool
     */
    protected $logErrors;

    /**
     * Page
     *
     * @var HtmlPageInterface|null
     */
    protected $page = null;

    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * Page resolver
     *
     * @var Closure
     */
    protected $pageResolver;

    /**
     * Constructor 
     * 
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     */
    public function __construct(Closure $pageResolver, ResponseFactoryInterface $responseFactory, bool $logErrors = true) 
    {       
        $this->pageResolver = $pageResolver;
        $this->responseFactory = $responseFactory; 
        $this->logErrors = $logErrors;       
    }

    /**
     * Get page ref
     *
     * @return HtmlPageInterface
     */
    protected function getPage()
    {
        if (empty($this->page) == true) {
            $this->page = ($this->pageResolver)();
        }

        return $this->page;
    }

    /**
     * Create error handler
     *
     * @return ErrorHandlerInterface
     */
    protected function createErrroHandler()
    {
        $errorRenderer = new HtmlPageErrorRenderer($this->getPage());

        return new ApplicationError($errorRenderer);  
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
        $errorHandler = $this->createErrroHandler();
        $response = $this->responseFactory->createResponse();

        if ($exception instanceof HttpException) {
            $request = $exception->getRequest();  
            $status = 404;                     
        } else {
            $status = 400;    
        }
       
        if (Install::isInstalled() == false) {                
            if (RouteType::isApiInstallRequest() == true) {
                return $handler->handle($request); 
            } elseif (RouteType::isInstallPage() == false) {
                $url = RouteType::getInstallPageUrl();                  
                return $response->withoutHeader('Cache-Control')
                    ->withHeader('Cache-Control','no-cache, must-revalidate')
                    ->withHeader('Content-Length','0')    
                    ->withHeader('Expires','Sat, 26 Jul 1997 05:00:00 GMT')        
                    ->withHeader('Location',$url)
                    ->withStatus(307);                  
            } 
           
            return $handler->handle($request);
        }
        // render errror
        $renderType = (Request::acceptJson($request) == true) ? 'json' : 'html';
        $output = $errorHandler->renderError($exception,$renderType);
        $response->getBody()->write($output);
        
        return $response->withStatus($status);
    }  
}
