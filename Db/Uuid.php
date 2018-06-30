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

use Arikaim\Core\Utils\Utils;

/**
 * Update UUID field
*/
trait Uuid 
{    
    public static function bootUuid()
    {
        static::creating(function($model) {   
            $model->uuid = Utils::getUUID();
        });
    }
}
