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

use Arikaim\Core\Utils\Utils;

/**
 *  Auth trait
 *  For change auth id name in model:  protected $auth_id_attribute = 'auth id name';
*/
trait Auth 
{   
    /**
     * Return Auth id name
     *
     * @return string
     */
    public function getAuthIdName()
    {
        return (isset($this->auth_id_attribute) == true) ? $this->auth_id_attribute : 'id';
    }

    /**
     * Return auth id
     *
     * @return mixed
     */
    public function getAuthId()
    {
        return $this->{$this->getAuthIdName()};
    }
}
