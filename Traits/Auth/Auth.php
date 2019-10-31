<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Traits\Auth;

use Arikaim\Core\Utils\Utils;

/**
 *  Auth trait
 *  For change auth id name in model:  protected $authIdColumn = 'auth id name';
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
        return (isset($this->authIdColumn) == true) ? $this->authIdColumn : 'id';
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
