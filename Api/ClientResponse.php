<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Api;

use Arikaim\Core\Utils\Utils;

/**
 * Rest Api Client Response
*/
class ClientResponse
{
    /**
     * Response data
     *
     * @var array
     */
    private $data;

    public function __construct($response_text) 
    {
        $this->data = null;
        if (Utils::isJson($response_text) == true) {
            $this->data = json_decode($response_text,true);
        }
    }

    /**
     * Return true if response is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        return (is_array($this->data) == true) ? true : false;         
    }

    /**
     * Return response result.
     *
     * @return array|mixed
     */
    public function getResult()
    {
        return ($this->isValid() == false) ? null : $this->data['result'];        
    }

    /**
     * Return tru if response have error 
     *
     * @return boolean
     */
    public function hasError()
    {
        if ($this->isValid() == false) {
            return null;
        }
        return (count($this->data['errors']) > 0) ? true : false;
    }

    /**
     * Return response errors
     *
     * @return array
     */
    public function getErrors()
    {
        return ($this->isValid() == false) ? [] : $this->data['errors'];       
    }

    /**
     * Return response code
     *
     * @return string
     */
    public function getCode()
    {
        return ($this->isValid() == false) ? null : $this->data['code'];           
    }

    /**
     * Return request execution time
     *
     * @return float
     */
    public function getExecutionTime()
    {
        if ($this->isValid() == false) {
            return null;
        }
        return (isset($this->data['execution_time']) == true) ? $this->data['execution_time'] : null;
    }

    /**
     * Get response as array
     *
     * @return array
     */
    public function toArray()
    {
        return ($this->isValid() == false) ? [] : $this->data;         
    }
}
