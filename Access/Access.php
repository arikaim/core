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

/**
 * Manage access.
 */
class Access 
{
    // route auth type
    const AUTH_NONE         = 0;
    const AUTH_BASIC        = 1;
    const AUTH_SESSION      = 2;
    const AUTH_JWT          = 3;
    const AUTH_CUSTOM_TOKEN = 4;

    // permissions type
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
    
    /**
     * Return class constant value
     *
     * @param string $constant_name Constant name.
     * @return mixed
    */
    public function get($constant_name)
    {
        return constant("Self::" . $constant_name);
    }
    
    /**
     * Initialize token
     *
     * @return void
     */
    private function initToken()
    {
        $this->token['decoded'] = [];
        $this->token['token'] = "";
        $this->token['valid'] = false;
        $this->token['type'] = Self::AUTH_NONE;
    }

    /**
     * Return true if token is valid
     *
     * @return boolean
     */
    public function isValidToken()
    {
        if (isset($this->token['valid']) == false) {
            return false;
        }
        return $this->token['valid'];
    }
    
    /**
     * Return true if token is not epmpty
     *
     * @return boolean
     */
    public function hasToken()
    {
        return !empty($this->token['token']);
    }

    /**
     * Return auth type
     *
     * @return int
     */
    public function getTokenAuthType()
    {
        if (empty($this->token['type']) == true) {
            return Self::AUTH_NONE;
        }
        return $this->token['type'];
    }

    /**
     * Return true if auth is JWT
     *
     * @return boolean
     */
    public function isJwtAuth()
    {
        return ($this->getTokenAuthType() == Self::AUTH_JWT) ? true : false;
    }

    /**
     * Return true if auth is session
     *
     * @return boolean
     */
    public function isSessionAuth()
    {
        return ($this->getTokenAuthType() == Self::AUTH_SESSION) ? true : false;          
    }

    /**
     * Return token array data
     *
     * @return array
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Return token param from decoded token
     *
     * @param string $name
     * @return mixed|null
     */
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

    /**
     * Return token type name
     *
     * @return string
     */
    public function getTokenTypeName()
    {
        return Self::getAuthName($this->token['type']);
    }

    /**
     * Retrive encoded token from request
     *
     * @param object $request
     * @return boolean
     */
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

    /**
     * Decode and save token data.
     *
     * @param string $token
     * @param int $type
     * @return boolean
     */
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

    /**
     * Return auth name
     *
     * @param int $auth
     * @return string
     */
    public function getAuthName($auth)
    {
        return (isset($this->auth_names[$auth]) == true) ? $this->auth_names[$auth] : false;          
    }

    /**
     * Return auth type code
     *
     * @param string $auth_name
     * @return int
     */
    public function getAuthType($auth_name)
    {
        $key = array_search($auth_name,$this->auth_names);
        return ($key === false) ? 0 : $key;           
    }

    /**
     * Check if auth name is valid 
     *
     * @param string $auth_name
     * @return boolean
     */
    public function isValidAuthName($auth_name)
    {
        $key = array_search($auth_name,$this->auth_names);
        return ($key === false) ? false : true;           
    }

    /**
     * Create auth token.
     *
     * @param int $user_id User Id
     * @param string $user_uuid User uuid
     * @param int $type Token type
     * @return object
     */
    public function createToken($user_id, $user_uuid, $type = Self::JWT_TOKEN) 
    {
        $jwt = new Jwt();
        $jwt->set('uuid',$user_uuid);
        $jwt->set('user_id',$user_id);       
        $token = $jwt->createToken();
        return $token;
    }

    /**
     * Get token from request header
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @return string|false Base64 encoded JSON Web Token, Session ID or false if not found.
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

    /**
     * Verify access
     *
     * @param int $auth Auth type code
     * @return boolean
     */
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

    /**
     * Return true if JWT token have valid user id 
     *
     * @return boolean
    */
    public function isValidUser()
    {
        if ($this->isValidToken() == false) {
            return false;
        }
        $model = Model::Users();

        $user_id = $this->getTokenParam('user_id');
        $uuid = $this->getTokenParam('uuid');

        $id = $model->validUUID($uuid);
        return ($id == $user_id) ? true : false;
    }

    /**
     * Check if current loged user have control panel permission
     *
     * @param string $uuid
     * @return boolean
     */
    public function hasControlPanelAccess($id = null)
    {
        return $this->hasPermission(Access::CONTROL_PANEL,ACCESS::FULL,$id);
    }
    
    /**
     * Check permission 
     *
     * @param string $name Permission name
     * @param array $type PermissionType (read,write,execute,delete)
     * @param string $uuid User UUID, if is null uuid from  current token are used.
     * @return boolean
     */
    public function hasPermission($name,array $type = Self::FULL, $id = null)
    {
        $permissions = Model::Permissions();
        if ($id == null) {
            $id = $this->getTokenParam('user_id');
        }
        if (empty($id) == true) {
            return false;
        }
    
        $permissions = $permissions->getUserPermission($name,$id); 
        if (is_object($permissions) == false) {
            // check for control panel permission
            return ($name == Access::CONTROL_PANEL) ? false : $this->hasControlPanelAccess($id);
        }
        
        $result = $permissions->hasPermissions($type);
        if ($result == false) {
            // check for control panel permission
            return ($name == Access::CONTROL_PANEL) ? false : $this->hasControlPanelAccess($id);  
        }  
        
        return true; 
    }

    /**
     * Remove token data.
     *
     * @return void
     */
    public function clearToken()
    {
        Arikaim::cookies()->set("token",null);  
        $this->initToken(); 
    }
}   
