<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Access;

use Arikaim\Core\Arikaim;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;

/**
 * JSON Web Token Authentication
*/
class Jwt
{
    /**
     * JWT object
     *
     * @var object
     */
    private $token;
    
    /**
     * JWT key
     *
     * @var strin
     */
    private $key;

    /**
     * Constructor
     *
     * @param int|null $expire_time Expire time stamp, default value 1 month
     */
    public function __construct($expire_time = null)
    {
        $this->init($expire_time);
    }

    /**
     * Init token data
     *
     * @param int|null $expire_time
     * @return void
     */
    private function init($expire_time = null) 
    {
        $this->key = Arikaim::config('settings/jwt_key');
        $expire_time = ($expire_time == null) ? strtotime("+1 week") : $expire_time;
        $token_id = base64_encode(random_bytes(32));
       
        $this->token = new Builder();
        $this->token->setIssuer(ARIKAIM_DOMAIN);
        $this->token->setAudience(ARIKAIM_DOMAIN);
        $this->token->setId($token_id, true);
        $this->token->setIssuedAt(time());
        $this->token->setNotBefore(time());
        $this->token->setExpiration($expire_time);
    }

    /**
     * Set token parameter
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key,$value) 
    {        
        $this->token->set($key,$value);
    }
    
    /**
     * Create JWT token
     *
     * @return string
     */
    public function createToken() 
    {    
        $signer = new Sha256();
        $this->token->sign($signer, $this->key);
        return (string)$this->token->getToken();
    }
    
    /**
     * Decode encrypted JWT token
     *
     * @param string $jwt_token
     * @param boolean $verify
     * @return boolean
     */
    public function decodeToken($jwt_token,$verify = true)
    {
        try {
            $parser = new Parser();
            $this->token = $parser->parse($jwt_token);      
            if ($verify == true) {
                if ($this->verify($this->key) == false) {
                    return false; // Token not valid
                }
            }
            return $this->token->getClaims();
        } catch(\Exception $e) {
            return false;
        }
    }

    /**
     * Verify token data
     *
     * @return boolean
     */
    public function verify() 
    {
        $signer = new Sha256();
        return $this->token->verify($signer,$this->key);
    }

    /**
     * Validate token data
     *
     * @param mixed $validation_data
     * @return void
     */
    public function validate($validation_data) 
    {
        return $this->token->validate($validation_data);
    }
}
