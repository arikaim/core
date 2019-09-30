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

/**
 * Increment field value
*/
trait IncrementValue 
{       
    /**
     * Increment  field value
     *
     * @param string|integer $uuid  Unique row id or uuid
     * @param string $field_name Field name
     * @param integer $increment 
     * @return integer
     */
    public function incrementValue($uuid, $field_name, $increment = 1)
    {        
        $model = (is_string($uuid) == true) ? parent::where('uuid','=',$uuid)->first() : parent::where('id','=',$uuid)->first();
          
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
