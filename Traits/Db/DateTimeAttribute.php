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

use Arikaim\Core\Utils\DateTime;

/**
 * Update created date field.
*/
trait DateTimeAttribute
{    

    public static function bootDateTimeAttribute()
    {
        static::creating(function($model) {   
            Self::updateAttribute('date_created',$model);
        });

        static::updating(function($model) {   
            Self::updateAttribute('date_updated',$model);
        });
            
    }
    
    /**
     * Set current timestamp to field
     *
     * @param [type] $name
     */
    public static function updateAttribute($name,$model)
    {
        if (isset($model->attributes[$name]) == false) {
            $model->attributes[$name] = DateTime::getCurrentTime();
        }
    }
}
