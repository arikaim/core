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

    public function findById($id)
    {
        if ($id == null || empty($id) == true) {
            return false;
        }
        if (is_numeric($id) == true) {
            $model = parent::where('id','=',$id)->first();
        } elseif (is_string($id) == true) {
            $model = parent::where('uuid','=',$id)->first();
        }
        return (is_object($model) == false) ? false : $model;
    }
}
