<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Controllers;

use Arikaim\Core\Api\Response;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Controllers\Controller;

/**
 * Base class for all Api controllers
*/
class ApiController extends Controller
{    
    /**
     * Api response
     *
     * @var object
     */
    protected $response;

    /**
     * Extension name
     *
     * @var string|null
     */
    protected $extension_name;

    /**
     * Model class name
     *
     * @var string
     */
    protected $model_class;

    /**
     * Constructor
     */
    public function __construct() 
    {
        parent::__construct();
        $this->type = Controller::API;
        $this->response = new Response(Arikaim::settings('debug'));  
           
        // set default validator error callback
        $this->onValidationError(function ($errors) {
            $this->setErrors($errors);
        });

        $this->extension_name = null;
        $this->model_class = null;
    }

    /**
     * Get extension name
     *
     * @return string|null
     */
    public function getExtensionName()
    {
        return $this->extension_name;
    }

    /**
     * Set extension name
     *
     * @param string $name
     * @return void
     */
    public function setExtensionName($name)
    {
        $this->extension_name = $name;
    }

    /**
     * Set model class name
     *
     * @param string $class
     * @return void
     */
    public function setModelClass($class)
    {
        $this->model_class = $class;
    }

    /**
     * Get model class name
     *     
     * @return string
     */
    public function getModelClass()
    {
        return $this->model_class;
    }

    /**
     * Add message to response, first find in messages array if not found display name value as message 
     *
     * @param string $name  
     * @return Response
     */
    public function message($name)
    {
        $message = $this->getMessage($name);
        $message = (empty($message) == true) ? $name : $message;
        return $this->response->message($message);       
    }

    /**
     * Set error, first find in messages array if not found display name value as error
     *
     * @param string $name
     * @return Response
     */
    public function error($name)
    {
        $message = $this->getMessage($name);
        $message = (empty($message) == true) ? $name : $message;
        return $this->response->setError($message);
    }

    /**
     * Set response field
     *
     * @param string $name
     * @param mixed $value
     * @return Response
     */
    public function field($name, $value)
    {
        return $this->response->field($name,$value);      
    }

    /**
     * Set response 
     *
     * @param boolean $condition
     * @param array|Closure $data
     * @param string|Closure $error
     * @return mixed
    */
    public function setResponse($condition, $data, $error)
    {
        if (is_string($error) == true) {
            $message = $this->getMessage($error);
            $error = (empty($message) == true) ? $error : $message;
        }
        if (is_string($data) == true) {
            $message = $this->getMessage($data);
            $data = (empty($message) == true) ? $data : $message;
        }
        return $this->response->setResponse($condition,$data,$error);
    }

    /**
     * Forward calls to $this->response and run Controller function if exist
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (is_callable([$this->response,$name]) == true) {
            return call_user_func_array([$this->response,$name], $arguments);     
        }
        //
        $callable = [$this,$name . 'Controller'];
        if (is_callable($callable) == true) {
            $callback = function($arguments) use(&$callable) {
                $callable($arguments[0],$arguments[1],$arguments[2]);
                return $this->getResponse();                 
            };
            return $callback($arguments);
        }
    }

    /**
     * Return response 
     *  
     * @param boolean $raw
     * 
     * @return Response
     */
    public function getResponse($raw = false)
    {
        return $this->response->getResponse($raw);
    }
}
