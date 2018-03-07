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

use Arikaim\Core\Arikaim;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class Jwt
{
    private $token;
    private $key;

    public function __construct($expire_time = null)
    {
        $this->init($expire_time);
    }

    private function init($expire_time) 
    {
        $this->key = Arikaim::config('settings/jwt_key');
        if ($expire_time == null) {
            $expire_time = strtotime("+1 month");
        }
        $token_id = base64_encode(random_bytes(32));
       
        $this->token = new Builder();
        $this->token->setIssuer(Arikaim::getDomain());
        $this->token->setAudience(Arikaim::getDomain());
        $this->token->setId($token_id, true);
        $this->token->setIssuedAt(time());
        $this->token->setNotBefore(time());
        $this->token->setExpiration($expire_time);
    }

    public function set($key,$value) 
    {        
        $this->token->set($key,$value);
    }
   
    public function createToken() 
    {    
        $signer = new Sha256();
        $this->token->sign($signer, $this->key);
        return (string)$this->token->getToken();
    }
   
    public function decodeToken($jwt_token,$verify = false)
    {
        try {
            $parser = new Parser();
            $this->token = $parser->parse($jwt_token);      
            if ($verify == true) {
                return $this->verify($this->key);
            }
            return $this->token->getClaims();
        } catch(\Exception $e) {
            return false;
        }
    }

    public function verify() 
    {
        $signer = new Sha256();
        return $this->token->verify($signer, $this->key);
    }

    public function validate($validation_data) 
    {
        return $this->token->validate($validation_data);
    }
}
