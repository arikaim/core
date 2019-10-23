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
    protected $display_error_details;

    /**
     * Show error trace
     *
     * @var boolean
     */
    protected $display_error_trace;

    /**
     * Log errors
     *
     * @var boolean
     */
    protected $log_errors;

    /**
     * Log error details
     *
     * @var boolean
     */
    protected $log_error_details;

    /**
     * Constructor
     *
     * @param boolean $display_error_details
     */
    public function __construct($display_error_details = true, $display_error_trace = true)
    {
        $this->display_error_details = $display_error_details;
        $this->display_error_trace = $display_error_trace;        
    }

    /**
     * Render error
     *
     * @param ServerRequestInterface $request   The most recent Request object
     * @param ResponseInterface      $response  The most recent Response object
     * @param \Throwable             $error     The caught Throwable object
     *
     * @return ResponseInterface   
     */
    public function renderError($request, $exception, $display_details, $log_errors, $log_error_details)
    {
        $this->log_errors = $log_errors;
        $this->display_error_details = $display_details;
        $this->log_error_details = $log_error_details;

        if (System::isConsole() == true) {
            return $this->renderConsoleErrorMessage($exception);
        } elseif (Request::acceptJson($request) == true) {
            return $this->renderJsonErrorMessage($exception);
        }

        $output = $this->renderHtmlErrorMessage($exception);       
        $response = Arikaim::getApp()->handle($request)->getBody()->write($output);

        return $response->withStatus(400);              
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

        if ($this->display_error_details == true) {
            System::writeLine('Type: ' . get_class($error));
            if ($error->getCode() == true) {
                System::writeLine('Code: ' . $error->getCode());
            }
            System::writeLine('');
            System::writeLine('Line: ' . $error->getLine());
            if ($this->display_error_trace == true) {
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
        $error_message = $this->renderHtmlError($error);

        $response->setError($error_message);
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
        $error_html = $this->renderHtmlError($error);
    
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
        Html::appendHtml($error_html);

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

        if ($this->display_error_details == true) {
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
            
            if ($this->display_error_trace == true) {
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
