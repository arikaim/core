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

trait DateTimeUpdate 
{    
    public function fill(array $attributes)
    {
        if (empty($attributes) == false ) {
            if (empty($attributes['created'] ) == true) {
                $attributes['created'] = DateTime::getCurrentTime();   
            }     
        }
        return parent::fill($attributes);
    }
}
