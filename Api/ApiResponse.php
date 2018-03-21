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
use Arikaim\Core\Arikaim;

class ApiResponse 
{
    protected $result;
    protected $response;  
    protected $errors; 
    protected $debug;
    protected $trace;
    protected $enableCors;

    public function __construct($response, $debug = false, $trace = false, $cors = true) 
    {                    
        $this->init($response,$debug,$trace);
        $this->enableCors = $cors;
    }

    private function init($response, $debug, $trace)
    {
        $this->errors = [];
        $this->debug = ($debug == true) ? true : false;
        $this->trace = ($trace == true) ? true : false;
        $this->response = $response;
        $this->result['result'] = "";
        $this->result['status'] = "ok";  
        $this->result['code'] = 200; 
        $this->result['errors'] = $this->errors;       
        $this->setResult("");
    }

    public function addErrors(array $errors)
    {
        if (is_array($errors) == true) {
            $this->errors = array_merge($this->errors,$errors);
            return true;
        }
        return false;
    }

    public function setErrors(array $errors)
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
        if ($this->hasError() == true) {
            $this->result['status'] = "error";
            $this->response->withStatus(401);
        } else {
            $this->result['status'] = "ok";
        }
        $this->result['code'] = $this->response->getStatusCode();

        if ($this->debug == true) {
            $this->result['execution_time'] = System::getExecutionTime();
        }
        if ($this->trace == true) {
            $this->result['trace'] = Utils::jsonEncode(System::getBacktrace());
        }
        $code = json_encode($this->result);
        $this->response->withHeader('Content-Type','application/json');
        // enable cors
        if ($this->enableCors == true) {
            $this->response->withHeader('Access-Control-Allow-Origin:','*');
        }
        $this->response->getBody()->write($code);
        return $this->response;
    }

    public function displayAuthError()
    {
        $this->setError(Arikaim::getError("AUTH_FAILED"));
        return $this->getResponse();
    }
}
