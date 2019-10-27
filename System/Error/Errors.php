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

use Arikaim\Core\Utils\Text;
use Arikaim\Core\Collection\Collection;
use Arikaim\Core\System\Config;
use Arikaim\Core\Api\Response;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Request;

/**
 * Errors
 */
class Errors extends Collection
{
    /**
     * Prefix
     *
     * @var string
     */
    private $prefix;

    /**
     * Errors
     *
     * @var array
     */
    private $errors;

    /**
     * Constructor
     */
    public function __construct() 
    {
        $this->errors = [];
        $this->loadErrorsConfig();
    }

    /**
     * Add error
     *
     * @param string $errorCode
     * @param array $params
     * @return bool
     */
    public function addError($errorCode, $params = [])
    {       
        $message = $this->getError($errorCode,$params);  
        $message = (empty($message) == true) ? $errorCode : $message;
         
        array_push($this->errors,$message);
        return true;
    }
    
    /**
     * Ger errors count
     *
     * @return integer
     */
    public function count()
    {
        return count($this->errors);
    }

    /**
     * Return true if have error
     *
     * @return boolean
     */
    public function hasError()
    {       
        return ($this->count() > 0) ? true : false;         
    }

    /**
     * Get errors list
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get error code
     *
     * @param string $errorCode
     * @param string|null $default
     * @param array $params
     * @return string
     */
    public function getError($errorCode, $params = [], $default = 'UNKNOWN_ERROR') 
    {
        $error = $this->get($errorCode,null);
        $error = (empty($error) == true) ? $this->get($default,null) : $error;

        return (empty($error) == true) ? null : Text::render($this->prefix . $error['message'], $params);      
    }

    /**
     * Get upload error message
     *
     * @param integer $errorCode
     * @return string
     */
    public function getUplaodFileError($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_OK:
                return "";// no error                
            case UPLOAD_ERR_INI_SIZE:
                return $this->getError("UPLOAD_ERR_INI_SIZE");
            case UPLOAD_ERR_FORM_SIZE:
                return $this->getError("UPLOAD_ERR_FORM_SIZE");
            case UPLOAD_ERR_PARTIAL:
                return $this->getError("UPLOAD_ERR_PARTIAL");
            case UPLOAD_ERR_NO_FILE:
                return $this->getError("UPLOAD_ERR_NO_FILE");
            case UPLOAD_ERR_NO_TMP_DIR:
                return $this->getError("UPLOAD_ERR_NO_TMP_DIR");
            case UPLOAD_ERR_CANT_WRITE:
                return $this->getError("UPLOAD_ERR_CANT_WRITE");
            case UPLOAD_ERR_EXTENSION:
                return $this->getError("UPLOAD_ERR_EXTENSION");
        }
        return "";
    }
    
    /**
     * Get posix error
     *
     * @return void
     */
    public static function getPosixError()
    {
        $err = posix_get_last_error();
        return ($err > 0) ? posix_strerror($err) : '';
    }

    /**
     * Get JSON error message
     *
     * @return string
     */
    public static function getJsonError()
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = null;
                break;
            case JSON_ERROR_DEPTH:
                $error = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $error = 'Unknown error';
                break;
        }
        return $error;
    }

    /**
     * Load error messages file.
     *
     * @return void
     */
    private function loadErrorsConfig() 
    {
        $list = Config::loadJsonConfigFile('errors.json');         
        $this->data = $list['errors'];
        $this->prefix = $list['prefix'];   
    }

    /**
     * Get error type text
     *
     * @param integer $errno
     * @return string
     */
    public static function getErrorTypeLabel($errno)
    {
        switch ($errno) {
            case E_USER_ERROR:
                return "USER ERROR";
            case E_USER_WARNING:
                return "WARNING";
            case E_USER_NOTICE:
                return "NOTICE";
            default:
                return "UNKNOW";
        }
    }

    /**
     * Show request error
     *
     * @param object $request
     * @param object $response
     * @param string $error
     * @param boolean $end
     * @return mixed
     */
    public function displayRequestError($request, $response, $error, $end = false)
    {
        Arikaim::logger()->alert($this->getError($error));   
        $this->addError($error);

        if (Request::acceptJson($request) == true) {
            $response = new Response();
            return $response->withError($error)->getResponse();           
        }       
        if ($end == true) {
            $response = Arikaim::page()->load('system:system-error');
            Arikaim::$app->respond($response); 
            Arikaim::end();
        }
       
        return Arikaim::page()->load('system:system-error'); 
    }
}
