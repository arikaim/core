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

trait UUIDAttribute 
{    
    public function fill(array $attributes)
    {
        if (empty($attributes) == false ) {
            if (empty($attributes['uuid'] ) == true) {
                $attributes['uuid'] = Utils::getUUID();   
            }     
        }
        return parent::fill($attributes);
    }
}
