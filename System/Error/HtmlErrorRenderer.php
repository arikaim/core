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

use Arikaim\Core\Utils\Html;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Interfaces\ErrorRendererInterface;

/**
 * Render error
 */
class HtmlErrorRenderer implements ErrorRendererInterface
{
    /**
     * Constructor
     *
     */
    public function __construct()
    {
    }

    /**
     * Render error
     *
     * @param array $errorDetails
     * @return void
     */
    public function render($errorDetails)
    {       
        try {
            switch($errorDetails['base_class']) {
                case 'HttpNotFoundException': {                   
                    $output = Arikaim::page()->renderPageNotFound(['error' => $errorDetails])->getHtmlCode();
                    break;
                }
                default: {                   
                    $output = Arikaim::page()->renderApplicationError(['error' => $errorDetails])->getHtmlCode();                       
                }
            }
            if (empty($output) == true) {
                $output = $this->renderSimplePage($errorDetails);
            }
        } catch(\Exception $exception) {
            $output = $this->renderSimplePage($exception);
        }
       
        echo $output;
    }

    /**
     * Render HTML error page
     *
     * @param array $errorDetails
     * @return void
     */
    protected function renderSimplePage($errorDetails)
    {
        $html = $this->renderHtmlError($errorDetails);
    
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

        echo Html::getDocument();
    }

    /**
     * Render error message
     *
     * @param array $errorDetails
     * @return string
     */
    protected function renderHtmlError($errorDetails)
    {
        Html::startDocument();

        Html::startDiv();
        Html::strong('Message: ');
        Html::endDiv($errorDetails['message']);
        Html::startDiv();
        Html::strong('File: ');
        Html::endDiv($errorDetails['file']);
        Html::startDiv();
        Html::strong('Type: ');
        Html::endDiv($errorDetails['type_text']);
       
        Html::startDiv();
        Html::strong('Code: ');
        Html::endDiv($errorDetails['code']);
      
        Html::startDiv();
        Html::strong('Line: ');
        Html::endDiv($errorDetails['line']);
        Html::h2('Trace: ');
        Html::pre($errorDetails['trace_text']);
        
        return Html::getDocument();
    }
}
