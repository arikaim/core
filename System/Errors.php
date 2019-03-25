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

use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\Collection;
use Arikaim\Core\System\Config;

class Errors extends Collection
{
    private $prefix;
    private $errors;

    public function __construct() 
    {
        $this->errors = [];
        $this->loadErrorsConfig();
    }

    public function addError($error_code,$params = [])
    {       
        $message = $this->getError($error_code,$params);  
        $message = (empty($message) == true) ? $error_code : $message;
         
        array_push($this->errors,$message);
        return true;
    }
    
    public function count()
    {
        return count($this->errors);
    }

    public function hasError()
    {       
        return ($this->count() > 0) ? true : false;         
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getError($error_code,$params = []) 
    {
        $error = $this->get($error_code,false);
        if ($error == false) {
            return "";
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
        $list = Config::loadJsonConfigFile('errors.json');         
        $this->data = $list['errors'];
        $this->prefix = $list['prefix'];   
    }
}
