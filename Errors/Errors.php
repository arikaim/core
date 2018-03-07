<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Errors;

use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\Collection;

class Errors extends Collection
{
    private $prefix;
    private $errors = [];

    public function __construct() 
    {
        $this->loadErrorsConfig();
    }

    public function addError($error_name,$params = [])
    {       
        $message = $this->getError($error_name,$params);  
        $this->errors[$error_name] = $message;
    }
    
    public function addErrorMessage($error_name, $message)
    {       
        $this->errors[$error_name] = $message;
    }

    public function errorsCount()
    {
        return count($this->errors);
    }

    public function hasError($error_name)
    {       
        if (isset($this->errors[$error_name]) == true) {
            return true;
        }
        return false;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getError($error_name,$params = []) 
    {
        $error = $this->get($error_name,false);
        if ($error == false) {
            return false;
        }
        $error_text = $this->prefix . $error['message'];
        return Utils::parseProperties($error_text,$params);
    }

    public function getUplaodFileError($error_code)
    {
        switch ($error_code) {
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
    
    public static function getJsonError()
    {
        $error = null;
   
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

    private function loadErrorsConfig() 
    {
        $list = File::readConfigFile('errors.json');  
        $this->data = $list['errors'];
        $this->prefix = $list['prefix'];       
    }
}
