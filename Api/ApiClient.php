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
use Arikaim\Core\Utils\Curl;
use Arikaim\Core\Api\ApiClientResponse;

/**
 * Api Client
*/
class ApiClient 
{
    private $host;
    private $token;
    private $timeout;
    private $headers;  
    private $requtest_url;

    public function __construct($host,$timeout = 30) 
    {
        $this->host = $host;
        $this->timeout = $timeout;
        $this->token = null;
        $this->headers = null;
        $this->api_response = null;
    }

    public function connect($api_key, $api_secret)
    {
        return $this->init($api_key,$api_secret);
    }

    public function init($api_key, $api_secret)
    {
        $params['api_secret'] = $api_secret; 
        $params['api_key'] = $api_key; 
        $response = $this->get('/api/create/token/',$params);

        if (Utils::isJSON($response) == false) {
            throw new \Exception("Server error or not valid requets url: " . $this->requtest_url);
        }

        $api_response = new ApiClientResponse($response);
        $this->token = $api_response->getResult();
        if ($this->token == null) {
            return false;
        }
        $this->setHeader("Authorization: ","Bearer " . $this->token);
        return true;
    }

    public function setHeader($name,$value)
    { 
        $this->headers[$name] = $value;
    }

    public function apiCall($path,$method,$params = [])
    {
        $this->requtest_url = $this->host . $path;
        $response = Curl::request($this->requtest_url,$method,$params,$this->headers,$this->timeout);      
        $api_response = new ApiClientResponse($response);
        return $api_response;
    }

    public function get($path,$params = [])
    {
        return $this->apiCall($path,"GET",$params);
    }
    
    public function post($path,$params = [])
    {
        return $this->apiCall($path,"POST",$params);
    }
    
    public function delete($path,$params = [])
    {
        return $this->apiCall($path,"DELETE",$params);
    }

    public function put($path,$params = [])
    {
        return $this->apiCall($path,"PUT",$params);
    }
}
