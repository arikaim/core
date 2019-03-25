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

    /**
     * Constructor
     *
     * @param string $host Host url
     * @param integer $timeout
     */
    public function __construct($host,$timeout = 30) 
    {
        $this->host = $host;
        $this->timeout = $timeout;
        $this->token = null;
        $this->headers = null;
        $this->api_response = null;
    }
    
    /**
     * Connect to remote host
     *
     * @param string $api_key User api key
     * @param string $api_secret User api secret
     * @return boolean
     */
    public function connect($api_key, $api_secret, $host = null)
    {
        if (empty($host) == false) {
            $this->setHost($host);
        }
        $params['api_secret'] = $api_secret; 
        $params['api_key'] = $api_key; 
        $response = $this->get('/api/create/token/',$params);

        if (Utils::isJSON($response) == false) {
            throw new \Exception("Server error or not valid requets url: " . $this->requtest_url);
        }

        $api_response = new ApiClientResponse($response);
        $this->token = $api_response->getResult();
        if (empty($this->token) == true) {
            return false;
        }
        $this->setHeader("Authorization: ","Bearer " . $this->token);
        return true;
    }

    /**
     *  Set remote host url
     *
     * @param string $host Remote host url.
     * @return void
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Set request header.
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function setHeader($name,$value)
    { 
        $this->headers[$name] = $value;
    }

    /**
     * Call rest api 
     *
     * @param string $path Url of rest api.
     * @param [type] $method Request method 
     * @param array $params 
     * @return object Response object
     */
    public function apiCall($path,$method,$params = [])
    {
        $this->requtest_url = $this->host . $path;
        $response = Curl::request($this->requtest_url,$method,$params,$this->headers,$this->timeout);      
        $api_response = new ApiClientResponse($response);
        return $api_response;
    }

    /**
     * Api reuest GET method 
     *
     * @param string $path Relative url path
     * @param array $params Request params
     * @return object
     */
    public function get($path,$params = [])
    {
        return $this->apiCall($path,"GET",$params);
    }
    
    /**
     * Api reuest POST method 
     *
     * @param string $path Relative url path
     * @param array $params Request params
     * @return object
     */
    public function post($path,$params = [])
    {
        return $this->apiCall($path,"POST",$params);
    }
    
    /**
     * Api request DELETE method
     *
     * @param string $path Relative url path
     * @param array $params Request params
     * @return object
     */
    public function delete($path,$params = [])
    {
        return $this->apiCall($path,"DELETE",$params);
    }

    /**
     * Api reuest PUT method 
     *
     * @param string $path Relative url path
     * @param array $params Request params
     * @return object
     */
    public function put($path,$params = [])
    {
        return $this->apiCall($path,"PUT",$params);
    }
}
