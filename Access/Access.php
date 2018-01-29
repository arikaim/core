<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Access;

use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Models;
use Arikaim\Core\Access\Jwt;


class Access 
{
    // route auth type
    const AUTH_NONE         = 0;
    const AUTH_BASIC        = 1;
    const AUTH_SESSION      = 2;
    const AUTH_JWT          = 3;
    const AUTH_CUSTOM_TOKEN = 4;

    private $token;
    private $auth_names = ["None","Basic","Session","JWT","CWT"];

    public function __construct() 
    {
        $this->initToken();
    }
    
    private function initToken()
    {
        $this->token['decoded'] = [];
        $this->token['token'] = "";
        $this->token['valid'] = false;
        $this->token['type'] = Self::AUTH_NONE;
    }

    public function isValidToken()
    {
        return $this->token['valid'];
    }
    
    public function hasToken()
    {
        return !empty($this->token['token']);
    }

    public function getTokenAuthType()
    {
        if (empty($this->token['type']) == true) {
            return Self::AUTH_NONE;
        }
        return $this->token['type'];
    }

    public function isJwtAuth()
    {
        if ($this->getTokenAuthType() == Self::AUTH_JWT) {
            return true;
        }
        return false;
    }

    public function isSessionAuth()
    {
        if ($this->getTokenAuthType() == Self::AUTH_SESSION) {
            return true;
        }
        return false;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getTokenParam($name)
    {
        if (isset($this->token['decoded'][$name]) == true) {
            return $this->token['decoded'][$name];
        }
        return null;
    }

    public function getTokenTypeName()
    {
        return Self::getAuthName($this->token['type']);
    }

    public function fetchToken($request) 
    {
        // get jwt token
        $jwt = new Jwt();
        $token = $this->readToken($request);
        
        if (empty($token) === false) {
            $decoded_token = $jwt->decodeToken($token);
            if ($decoded_token !== false) { 
                $this->token['decoded'] = $decoded_token;
                $this->token['token'] = $token;
                $this->token['valid'] = true;
                $this->token['type'] = Self::AUTH_JWT;
                return true;
            }
        }
      
       // $request_session_id = Arikaim::cookies()->get('PHPSESSID');
        $session_id = Arikaim::session()->getID();
        if ($token == $session_id) {
            $this->token['token'] = $token;
            $this->token['valid'] = true;
            $this->token['decoded'] = [];
            $this->token['type'] = Self::AUTH_SESSION;
            return true;
        }
        
        $this->initToken();
        return false;
    }

    public function getAuthName($auth)
    {
        if (isset($this->auth_names[$auth]) == true) {
            return $this->auth_names[$auth];
        }        
        return false;
    }

    public function getAuthType($auth_name)
    {
        $key = array_search($auth_name,$this->auth_names);
        if ($key === false) { 
            $key = 0;
        }
        return $key;
    }

    public function isValidAuthName($auth_name)
    {
        $key = array_search($auth_name,$this->auth_names);
        if ($key === false) {
            return false;
        }
        return true;
    }

    public function createToken(array $data, $type = Self::AUTH_JWT) {

    }

    /**
     * Get token
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @return string|null Base64 encoded JSON Web Token, Session ID or false if not found.
     */
    protected function readToken($request)
    {
        $header = "";       
        if (empty($header) == true) {
            $headers = $request->getHeader('Authorization');
            $header = isset($headers[0]) ? $headers[0] : "";
        }

        if (empty($header) && function_exists("apache_request_headers")) {
            $headers = apache_request_headers();
            $header = isset($headers['Authorization']) ? $headers['Authorization'] : "";
        }

        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

       // $jwt_token = Arikaim::cookies()->get('jwt_token');
      //  if (isset($jwt_token) == true) {            
      //      return $jwt_token;
      //  };      
        return false;
    }

    public function checkAccess($auth)
    {
        if (is_numeric($auth) == false) {
            $auth = $this->getAuthType($auth);
        }
     
        $result = false;
        switch($auth) {
            case Self::AUTH_JWT: {
                $result = $this->isJwtAuth();                
                break;
            }
            case Self::AUTH_SESSION: {
                $result = $this->isValidToken();                
                break;
            }
            default: {
                $result = true;
            }
        }
        return $result;
    }
}   
