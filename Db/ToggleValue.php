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
 * Update boolean database values (true or false)
*/
trait ToggleValue 
{       
    public function toggleValue($uuid, $field_name)
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
        $value = ($value == 0) ? 1 : 0;
         
        $model->setAttribute($field_name,$value);
        $model->update();        
        return $value;
    }
}
