<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\Error;

use Arikaim\Core\Arikaim;
use Slim\Http\Body;

class ApplicationError extends Error
{
    public static function show($exception)
    {
        $self = new Self();
        $self->handle(Arikaim::request(),Arikaim::response(),$exception);
    }
    
    public function handle($request, $response, $exception)
    {
        $contentType = $this->determineContentType($request);
        switch ($contentType) {
            case 'application/json':
                $output = $this->renderJsonErrorMessage($exception);
                break;

            case 'text/html':
                $output = $this->renderHtmlErrorMessage($exception);
                break;
        }
        echo $output;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response,\Exception $exception)
    {
        return $this->handle($request,$response,$exception);
    }

    /**
     * Render HTML error page
     *
     * @param  \Exception $exception
     *
     * @return string
     */
    protected function renderHtmlErrorMessage(\Exception $exception)
    {
        $title = 'Application Error';
        $html = '<h3>Details</h3>';
        $html .= $this->renderHtmlException($exception);

        while ($exception = $exception->getPrevious()) {
            $html .= '<h4>Previous exception</h4>';
            $html .= $this->renderHtmlExceptionOrError($exception);
        }

        $output = sprintf(
            "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
            "<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana," .
            "sans-serif;}strong{display:inline-block;width:65px;}</style></head><body><h2>%s</h2>%s</body></html>",
            $title,
            $title,
            $html
        );

        return $output;
    }

    /**
     * Render exception as HTML.
     *
     * Provided for backwards compatibility; use renderHtmlExceptionOrError().
     *
     * @param \Exception $exception
     *
     * @return string
     */
    protected function renderHtmlException(\Exception $exception)
    {
        return $this->renderHtmlExceptionOrError($exception);
    }

    /**
     * Render exception or error as HTML.
     *
     * @param \Exception|\Error $exception
     *
     * @return string
     */
    protected function renderHtmlExceptionOrError($exception)
    {
        $html = sprintf('<div><strong>Type:</strong> %s</div>', get_class($exception));
        if (($code = $exception->getCode())) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }
        if (($message = $exception->getMessage())) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', htmlentities($message));
        }
        if (($file = $exception->getFile())) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }
        if (($line = $exception->getLine())) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }
        if (($trace = $exception->getTraceAsString())) {
            $html .= '<h4>Trace</h4>';
            $html .= sprintf('<pre>%s</pre>', htmlentities($trace));
        }
        return $html;
    }

    /**
     * Render JSON error
     *
     * @param \Exception $exception
     *
     * @return string
     */
    protected function renderJsonErrorMessage(\Exception $exception)
    {
        $error = [
            'message' => 'Application Error',
        ];

        if ($this->displayErrorDetails) {
            $error['exception'] = [];
            do {
                $error['exception'][] = [
                    'type' => get_class($exception),
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => explode("\n", $exception->getTraceAsString()),
                ];
            } while ($exception = $exception->getPrevious());
        }
        return json_encode($error, JSON_PRETTY_PRINT);
    }
}
