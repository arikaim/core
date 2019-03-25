<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Traits\Db;

/**
 * Update Status field
*/
trait Status 
{        
    protected $status_attribute = 'status';

    static $DISABLED = 0;
    static $ACTIVE = 1;  
    static $COMPLETED = 2;  

    public static function ACTIVE()
    {
        return Self::$ACTIVE;
    }

    public static function DISABLED()
    {
        return Self::$DISABLED;
    }

    public static function COMPLETED()
    {
        return Self::$COMPLETED;
    }

    public function getActive()
    {
        return parent::where($this->status_attribute,'=',Self::ACTIVE());
    }
    
    public function getDisabled()
    {
        return parent::where($this->status_attribute,'=',Self::DISABLED());
    }

    public function setStatus($status)
    {
        if (isset($this->attributes[$this->status_attribute]) == true) {
            $this->attributes[$this->status_attribute] = $status;           
            return $this->save();
        }
        return false;
    }
}
