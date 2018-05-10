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

use Slim\Http\Response;
use Arikaim\Core\System\System;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Arikaim;

/**
 * Api Respnse support JSON format only.
*/
class ApiResponse 
{
    protected $result;
    protected $errors; 
    protected $debug;
    protected $trace;
    protected $enableCors;
    protected $pretty_format;

    /**
     * Constructor
     *
     * @param boolean $debug
     * @param boolean $trace
     * @param boolean $cors
     */
    public function __construct($debug = false, $trace = false, $cors = true) 
    {                    
        $this->init($debug,$trace);
        $this->enableCors = $cors;
    }

    /**
     * Initialize
     *
     * @param boolean $debug Add debug info to response
     * @param boolean $trace Add trace info to response
     * @return void
     */
    private function init($debug, $trace)
    {
        $this->errors = [];
        $this->debug = ($debug == true) ? true : false;
        $this->trace = ($trace == true) ? true : false;
        $this->result['result'] = "";
        $this->result['status'] = "ok";  
        $this->result['code'] = 200; 
        $this->result['errors'] = $this->errors;  
        $this->pretty_format = false;     
        $this->setResult("");
    }

    /**
     * Add errors
     *
     * @param array $errors
     * @return boolean
     */
    public function addErrors(array $errors)
    {
        if (is_array($errors) == false) {
            return false;
        }
        $this->errors = array_merge($this->errors,$errors);
        return true;
    }

    /**
     * Set errors 
     *
     * @param array $errors
     * @return boolean
     */
    public function setErrors(array $errors)
    {
        if (is_array($errors) == true) {
            $this->errors = $errors;
            return true;
        }
        return false;
    }

    /**
     * Clear all errors.
     *
     * @return void
    */
    public function clearErrors()
    {
        $this->errors = [];
    }

    /**
     * Set error message
     *
     * @param string $error_message
     * @return void
     */
    public function setError($error_message) 
    {
        array_push($this->errors,$error_message);   
    }

    /**
     * Set response result
     *
     * @param mixed $result_code
     * @param boolean $pretty_format JSON pretty format
     * @return void
     */
    public function setResult($result_code, $pretty_format = false) 
    {
        $this->result['result'] = $result_code;
        $this->pretty_format = $pretty_format;
    }

    /**
     * Return errors count
     *
     * @return int
     */
    public function getErrorCount()
    {
        return count($this->errors);
    }

    /**
     * Return true if response have error
     *
     * @return boolean
     */
    public function hasError() 
    {    
        return ($this->getErrorCount() > 0) ? true : false;          
    }

    /**
     * Return request response
     *
     * @return Slim\Http\Response
     */
    public function getResponse() 
    {    
        $response = new Response();
        $this->result['errors'] = $this->errors;
        if ($this->hasError() == true) {
            $this->result['status'] = "error"; 
            $this->result['code'] = 401;
        }
        
        if ($this->debug == true) {
            $this->result['execution_time'] = System::getExecutionTime();
        }
        if ($this->trace == true) {
            $this->result['trace'] = Utils::jsonEncode(System::getBacktrace());
        }

        if ($this->pretty_format == true) {
            $code = Utils::jsonEncode($this->result);
        } else {
            $code = json_encode($this->result,true);
        }
        // enable cors
        if ($this->enableCors == true) {
            $response = $response->withHeader('Access-Control-Allow-Origin:','*');
        }
        return $response->withStatus($this->result['code'])
            ->withHeader('Content-Type','application/json')
            ->write($code);
    }

    /**
     * Show Auth error
     *
     * @return void
     */
    public function displayAuthError()
    {
        $this->setError(Arikaim::getError("AUTH_FAILED"));
        return $this->getResponse();
    }
}
