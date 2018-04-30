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

/**
 * Increment field value
*/
trait IncrementValue 
{       
    public function incrementValue($uuid, $field_name, $increment = 1)
    {        
        if (is_string($uuid) == true) {
            $model = parent::where('uuid','=',$uuid)->first();
        } else {
            $model = parent::where('id','=',$uuid)->first();
        }
        
        if (is_object($model) == false) {
            return false;
        }
        $value = $model->getAttribute($field_name);
        $value += $increment;

        $model->setAttribute($field_name,$value);
        $model->update();        
        return $value;
    }
}
