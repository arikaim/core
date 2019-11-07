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

use Arikaim\Core\Utils\Uuid as UuidFactory;

/**
 * Update UUID field
 *      
*/
trait Uuid 
{    
    /**
     * Init model events.
     *
     * @return void
     */
    public static function bootUuid()
    {
        static::creating(function($model) {   
            if (empty($model->attributes[$model->getUuidAttributeName()]) == true) {  
                $model->attributes[$model->getUuidAttributeName()] = UuidFactory::create();
            }
        });
    }

    /**
     * Get uuid attribute name
     *
     * @return string
     */
    public function getUuidAttributeName()
    {
        return (isset($this->uuidColumn) == true) ? $this->uuidColumn : 'uuid';
    }
}
