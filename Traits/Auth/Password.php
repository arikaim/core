<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Traits\Auth;

/**
 *  Password trait
 *  Change password attribute name in model: protected $password_attribute = 'password';
 *  Chage encrypth algo: protected $password_encrypt_algo = algo | null  
*/
trait Password 
{   
    /**
     * Encrypt password
     *
     * @param string $password
     * @param integer $algo
     * @return string
     */
    public function encryptPassword($password, $algo = null) 
    {
        $algo = ($algo == null) ? $this->getEncryptPasswordAlgo() : $algo;

        return (empty($algo) == true) ? $password : password_hash($password,$algo);
    }

    /**
     * Change password
     *
     * @param string|integer $id
     * @param string $password
     * @return bool
     */
    public function changePassword($id, $password)
    {       
        $model = $this->findById($id);
        if (is_object($model) == false) {
            return false;
        }
        $model->{$this->getPasswordAttributeName()} = $this->encryptPassword($password);    
        return $model->save();
    }    

    /**
     * Return true if password is correct.
     *
     * @param string $password
     * @param string|null $hash
     * @return bool
     */
    public function verifyPassword($password, $hash = null) 
    {
        if (empty($password) == true ) {
            return false;
        }
        $hash = (empty($hash) == true) ? $this->getPassword() : $hash;
        $algo = $this->getEncryptPasswordAlgo();
        return (empty($algo) == true) ? ($password == $hash) : password_verify($password,$hash);      
    }

    /**
     * Return password attribute name
     *
     * @return string
     */
    public function getPasswordAttributeName()
    {
        return (isset($this->password_attribute) == true) ? $this->password_attribute : 'password';
    }

    /**
     * Return encrypt algo
     *
     * @return mixed
     */
    public function getEncryptPasswordAlgo()
    {
        return (isset($this->password_encrypt_algo) == true) ? $this->password_encrypt_algo : PASSWORD_BCRYPT;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->{$this->getPasswordAttributeName()};
    }
}
