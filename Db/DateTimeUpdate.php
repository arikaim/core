<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Db;

use Arikaim\Core\Utils\DateTime;

/**
 * Update created date field.
*/
trait DateTimeUpdate 
{    
    public function fill(array $attributes)
    {
        if (empty($attributes) == false ) {
            $attributes = $this->updateAttribute('date_created',$attributes);   
            $attributes = $this->updateAttribute('date_updated',$attributes);   
            $attributes = $this->updateAttribute('date_login',$attributes);  
        }
        return parent::fill($attributes);
    }

    public function updateAttribute($name, array $attributes)
    {
        if (isset($attributes[$name]) == false) {
            $attributes[$name] = DateTime::getCurrentTime();
        }
        if (empty($attributes[$name]) == true) {
            $attributes[$name] = DateTime::getCurrentTime();   
        }
        return $attributes;
    }
}
