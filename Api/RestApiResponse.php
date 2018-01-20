<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Api;

use Arikaim\Core\System\System;
use Arikaim\Core\Utils\Utils;

class RestApiResponse 
{
    protected $result;
    protected $response;  
    protected $errors; 
    protected $debug;
    protected $trace;

    public function __construct($response, $debug = false, $trace = false) 
    {                    
        $this->errors = [];
        $this->debug = ($debug == true) ? true : false;
        $this->trace = ($trace == true) ? true : false;
        $this->response = $response;
        $this->result['result'] = "";
        $this->result['status'] = "ok";   
        $this->result['errors'] = $this->errors;       
        $this->setResult("");
    }

    public function addErrors($errors)
    {
        if (is_array($errors) == true) {
            $this->errors = array_merge($this->errors,$errors);
            return true;
        }
        return false;
    }

    public function setErrors($errors)
    {
        if (is_array($errors) == true) {
            $this->errors = $errors;
            return true;
        }
        return false;
    }

    public function clearErrors()
    {
        $this->errors = [];
    }

    public function setError($error_message) 
    {
        array_push($this->errors,$error_message);       
    }

    public function setResult($result_code) 
    {
        $this->result['result'] = $result_code;
    }

    public function getErrorCount()
    {
        return count($this->errors);
    }

    public function hasError() 
    {    
        if ($this->getErrorCount() > 0 ) {
            return true;
        }
        return false;
    }

    public function getResponse() 
    {    
        $this->result['errors'] = $this->errors;
        if ( $this->hasError() == true ) {
            $this->result['status'] = "error";
            $this->response->withStatus(401);
        } else {
            $this->result['status'] = "ok";
        }
       
        if ($this->debug == true) {
            $this->result['execution_time'] = System::getExecutionTime();
        }
        if ($this->trace == true) {
            $this->result['trace'] = Utils::jsonEncode(System::getBacktrace());
        }
        $code = Utils::jsonEncode($this->result);
        $this->response->withHeader('Content-Type', 'application/json');
        $this->response->getBody()->write($code);
        return $this->response;
    }
}
