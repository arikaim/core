<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Traits\Db;

use Arikaim\Core\Utils\DateTime;

/**
 * Set current time for date created.
 * 
 * Change default date created attribute in model class 
 *      protected $date_created_attribute = 'db column name';
*/
trait DateCreated
{    
    /**
     * Set model events
     *
     * @return void
     */
    public static function bootDateCreated()
    {
        static::creating(function($model) {  
            $name = $model->getDateCreatedAttributeName();   
            $model->attributes[$name] = DateTime::getCurrentTime();                   
        });
    }
    
    /**
     *  Get date created attribute
     *
     * @return string
     */
    public function getDateCreatedAttributeName()
    {
        return (isset($this->date_created_attribute) == true) ? $this->date_created_attribute : 'date_created';
    }
}
