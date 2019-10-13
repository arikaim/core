<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Utils;

/**
 * Request helpers
 */
class Request 
{  
    /**
     * Return content type 
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param string $default
     * @return void
     */
    public static function getContentType($request, $default = 'text/html')
    {
        $content = $request->getContentType();
        if (empty($content) == true) {
            $accept = $request->getHeaderLine('Accept');
            $tokens = \explode(',',$accept);
            if (empty($tokens[0]) == false) {
                return $tokens[0];
            }
        }

        return (empty($content) == true) ? $default : $content;
    }

    /**
     * Return true if content type is json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return boolean
     */
    public static function isJsonContentType($request)
    {
        $content = Self::getContentType($request);
        return (substr($content,-4) == 'json') ? true : false;
    }
    
    /**
     * Return true if content type is xml
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return boolean
     */
    public static function isXmlContentType($request)
    {
        $content = Self::getContentType($request);
        return (substr($content,-3) == 'xml') ? true : false;
    }

    /**
     * Return true if content type is html
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return boolean
     */
    public static function isHtmlContentType($request)
    {
        $content = Self::getContentType($request);
        return (substr($content,-4) == 'html') ? true : false;
    }

    /**
     * Parse accept header
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return array
     */
    public static function parseAcceptHeader($request)
    {
        $accept = $request->getHeaderLine('Accept');
        $parts = explode(';',$accept);
        return explode(',',$parts[0]);
    }

    /**
     * Return true if request accept json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return boolean
     */
    public static function acceptJson($request)
    {
        $content_types = Self::parseAcceptHeader($request);
        foreach ($content_types as $item) {
            if (substr($item,-4) == 'json') {
                return true;
            }
        }
        return false;
    }

    /**
     * Return true if request accept xml
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return boolean
     */
    public static function acceptXml($request)
    {
        $content_types = Self::parseAcceptHeader($request);
        foreach ($content_types as $item) {
            if (substr($item,-3) == 'xml') {
                return true;
            }
        }
        return false;
    }

    /**
     * Get browser name
     *
     * @return string
     */
    public static function getBrowserName()
    {      
        $user_agent =  " " . strtolower($_SERVER['HTTP_USER_AGENT']);
        switch ($user_agent) {
            case (strpos($user_agent,'opera') != false):
                return 'Opera';                
            case (strpos($user_agent,'edge') != false):
                return 'Edge';
            case (strpos($user_agent,'firefox') != false):
                return 'Firefox';    
            case (strpos($user_agent,'chrome') != false):
                return 'Chrome';  
            case (strpos($user_agent,'safari') != false):
                return 'Safari';    
            case (strpos($user_agent,'msie') != false):
                return 'Internet Explorer';  
            case (strpos($user_agent,'mobile') != false):
                return 'Mobile Browser'; 
            case (strpos($user_agent,'android') != false):
                return 'Mobile Browser';                
            default: 
                return null;
        }       
    }
}
