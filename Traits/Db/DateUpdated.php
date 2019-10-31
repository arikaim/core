<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Traits\Db;

use Arikaim\Core\Utils\DateTime;

/**
 * Set current time for date updated.
 * 
 * Change default date updated attribute
 *      protected $dateUpdatedColumn = 'db column name';
*/
trait DateUpdated
{    
    /**
     * Set model events
     *
     * @return void
     */
    public static function bootDateUpdated()
    {
        static::updating(function($model) {   
            $name = $model->getDateUpdatedAttributeName();             
            $model->attributes[$name] = DateTime::getCurrentTime();             
        });
    }
    
    /**
     * Get date updated attribute
     *
     * @return string
     */
    public function getDateUpdatedAttributeName()
    {
        return (isset($this->dateUpdatedColumn) == true) ? $this->dateUpdatedColumn : 'date_updated';
    }
}
