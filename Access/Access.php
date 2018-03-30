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
use Arikaim\Core\Db\Model;
use Arikaim\Core\Access\Jwt;

class Access 
{
    // route auth type
    const AUTH_NONE         = 0;
    const AUTH_BASIC        = 1;
    const AUTH_SESSION      = 2;
    const AUTH_JWT          = 3;
    const AUTH_CUSTOM_TOKEN = 4;

    // permissions
    const FULL = ['read','write','delete','execute'];
    const READ = ['read'];
    const WRITE = ['write'];
    const DELETE = ['delete'];
    const EXECUTE = ['execute'];

    const CONTROL_PANEL = "ControlPanel";
    
    // tokens 
    const JWT_TOKEN = 1;
    const CUSTOM_TOKEN = 2;
    
    private $token;
    private $auth_names = ["None","Basic","Session","JWT","CWT"];

    public function __construct() 
    {
        $this->initToken();
    }
    
    public function get($constant_name)
    {
        return constant("Self::" . $constant_name);
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
        if (isset($this->token['valid']) == false) {
            return false;
        }
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
        if (isset($this->token['decoded'][$name]) == false) {
            return null;
        }
        if (is_object($this->token['decoded'][$name]) == true) {            
            return $this->token['decoded'][$name]->getValue();
        }
        return null;
    }

    public function getTokenTypeName()
    {
        return Self::getAuthName($this->token['type']);
    }

    public function fetchToken($request) 
    {
        $token = $this->readToken($request);
        if (empty($token) === true) {
            return false;
        }

        $result = $this->applyToken($token,Self::AUTH_JWT);
        if ($result == false) {
            if ($token == Arikaim::session()->getId()) {
                return $this->applyToken($token,Self::AUTH_SESSION);
            }
        }
        return false;
    }

    public function applyToken($token, $type = Self::AUTH_JWT)
    {
        switch ($type) {
            case Self::AUTH_SESSION: {
                $decoded = [];
                break;
            }
            case Self::AUTH_JWT: {
                $jwt = new Jwt();
                $decoded = $jwt->decodeToken($token);
                break;
            }
            default : {
                $decoded = [];
            }
        }
        $valid = ($decoded !== false) ? true : false;

        $this->token['token'] = $token;
        $this->token['valid'] = $valid;
        $this->token['decoded'] = $decoded;
        $this->token['type'] = $type;
        return $valid;
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

    public function createToken($user_id, $user_uuid, $type = Self::JWT_TOKEN) 
    {
        $jwt = new Jwt();
        $jwt->set('uuid',$user_uuid);
        $jwt->set('user_id',$user_id);       
        $token = $jwt->createToken();
        return $token;
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

    public function isValidUser()
    {
        if ($this->isValidToken() == false) {
            return false;
        }
        $model = Model::Users();

        $user_id = $this->getTokenParam('user_id');
        $uuid = $this->getTokenParam('uuid');

        $id = $model->validUUID($uuid);
        if ($id == $user_id) {
            return true;
        }
        return false;
    }

    public function hasControlPanelAccess($uuid = null)
    {
        return $this->hasPermission(Access::CONTROL_PANEL,ACCESS::FULL,$uuid);
    }

    public function hasPermission($name, $access = Self::FULL,$uuid = null)
    {
        $result = false;
        $permissions = Model::Permissions();
        if ($uuid == null) {
            $uuid = $this->getTokenParam('uuid');
        }
        $permissions = $permissions->getPermission($name,$uuid);       
        if (is_object($permissions) == true) {
            $result = $permissions->hasPermissions($access);
        }              
        return $result;
    }

    public function clearToken()
    {
        Arikaim::cookies()->set("token",null);  
        $this->initToken(); 
    }
}   
