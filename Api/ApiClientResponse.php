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

use Arikaim\Core\Utils\Utils;

/**
 * Api Client Response
*/
class ApiClientResponse
{
    private $response_text;
    private $data;

    public function __construct($response_text) 
    {
        $this->data = null;
        if (Utils::isJSON($response_text) == true) {
            $this->data = json_decode($response_text,true);
        }
    }

    public function isValid()
    {
        if (is_array($this->data) == true) {
            return true;
        }
        return false;
    }

    public function getResult()
    {
        if ($this->isValid() == false) {
            return null;
        }
        return $this->data['result'];
    }

    public function hasError()
    {
        if ($this->isValid() == false) {
            return null;
        }
        if (count($this->data['errors']) > 0) {
            return true;
        }
        return false;
    }

    public function getErrors()
    {
        if ($this->isValid() == false) {
            return [];
        }
        return $this->data['errors'];
    }

    public function getCode()
    {
        if ($this->isValid() == false) {
            return null;
        }
        return $this->data['code'];
    }

    public function getExecutionTime()
    {
        if ($this->isValid() == false) {
            return null;
        }
        if (isset($this->data['execution_time']) == true) {
            return $this->data['execution_time'];
        }
        return null;
    }

    public function toArray()
    {
        if ($this->isValid() == false) {
            return [];
        }
        return $this->data;
    }
}
