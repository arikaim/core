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

use Arikaim\Core\System\System;
use Arikaim\Core\Utils\Html;
use Arikaim\Core\Utils\Request;
use Arikaim\Core\Api\Response;
use Arikaim\Core\Arikaim;

/**
 * Php error base class
 */
class PhpError
{
    /**
     * Show error details
     *
     * @var boolean
     */
    protected $displayErrorDetails;

    /**
     * Show error trace
     *
     * @var boolean
     */
    protected $displayErrorTrace;

    /**
     * Log errors
     *
     * @var boolean
     */
    protected $logErrors;

    /**
     * Log error details
     *
     * @var boolean
     */
    protected $logErrorDetails;

    /**
     * Constructor
     *
     * @param boolean $displayErrorDetails
     */
    public function __construct($displayErrorDetails = true, $displayErrorTrace = true)
    {
        $this->displayErrorDetails = $displayErrorDetails;
        $this->displayErrorTrace = $displayErrorTrace;        
    }

    /**
     * Render error
     *
     * @param ServerRequestInterface $request   The most recent Request object    
     * @param \Throwable             $exception     The caught Throwable object
     * @param bool $displayDetails
     * @param bool $logErrors
     * @param bool $logErrorDetails
     * @return ResponseInterface   
     */
    public function renderError($request, $exception, $displayDetails, $logErrors, $logErrorDetails)
    {
        $this->logErrors = $logErrors;
        $this->displayErrorDetails = $displayDetails;
        $this->logErrorDetails = $logErrorDetails;

        if (System::isConsole() == true) {
            return $this->renderConsoleErrorMessage($exception);
        } elseif (Request::acceptJson($request) == true) {
            return $this->renderJsonErrorMessage($exception);
        }

        $output = $this->renderHtmlErrorMessage($exception);       
        
        $response = Arikaim::response()->withStatus(400);
        $response->getBody()->write($output);   
        
        return $response;              
    }

    /**
     * Render error
     *
     * @param \Throwable $error
     * 
     * @return ResponseInterface
     */
    public function renderConsoleErrorMessage($error)
    {
        System::writeLine('');
        System::writeLine('Application error');
        System::writeLine('Message: ' . $error->getMessage());
        System::writeLine('File: ' . $error->getFile());

        if ($this->displayErrorDetails == true) {
            System::writeLine('Type: ' . get_class($error));
            if ($error->getCode() == true) {
                System::writeLine('Code: ' . $error->getCode());
            }
            System::writeLine('');
            System::writeLine('Line: ' . $error->getLine());
            if ($this->displayErrorTrace == true) {
                System::writeLine('Trace: ' . $error->getTraceAsString());
            }
            while ($error = $error->getPrevious()) {
                System::writeLine('');
                System::writeLine('Previous error');         
                System::showConsoleOutput($error);
            }
        }
    }

    /**
     * Render error message
     *
     * @param \Throwable $error
     * 
     * @return ResponseInterface
     */
    protected function renderJsonErrorMessage($error)
    {
        $response = new Response();
        $message = $this->renderHtmlError($error);

        $response->setError($message);
        return $response->getResponse();
    }

    /**
     * Render HTML error page
     *
     * @param \Throwable $error
     *
     * @return string
     */
    protected function renderHtmlErrorMessage($error)
    {
        $html = $this->renderHtmlError($error);
    
        $title = 'Application Error';    
        Html::startDocument();
        Html::startHtml();
        Html::startHead();
        Html::appendHtml("<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>");
        Html::title($title);
        Html::style("body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana," .
            "sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{" .
            "display:inline-block;width:65px;}");
        Html::endHead();
        Html::startBody();
        Html::h1($title);

        Html::h2('Details');
        Html::appendHtml($html);

        Html::endBody();    
        Html::endHtml();

        return Html::getDocument();
    }

    /**
     * Render error message
     *
     * @param \Throwable $error
     * @return string
     */
    protected function renderHtmlError($error)
    {
        Html::startDocument();

        Html::startDiv();
        Html::strong('Message: ');
        Html::endDiv(htmlentities($error->getMessage()));

        Html::startDiv();
        Html::strong('File: ');
        Html::endDiv($error->getFile());

        if ($this->displayErrorDetails == true) {
            Html::startDiv();
            Html::strong('Type: ');
            Html::endDiv(get_class($error));

            if ($error->getCode() == true) {
                Html::startDiv();
                Html::strong('Code: ');
                Html::endDiv($error->getCode());
            }

            Html::startDiv();
            Html::strong('Line: ');
            Html::endDiv($error->getLine());
            
            if ($this->displayErrorTrace == true) {
                Html::h2('Trace: ');
                Html::pre($error->getTraceAsString());
            }

            while ($error = $error->getPrevious()) {
                Html::h2('Previous error');         
                $html = $this->renderHtmlError($error);
                Html::appendHtml($html);
            }
        }

        return Html::getDocument();
    }
}
