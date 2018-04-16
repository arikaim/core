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
        $model = parent::where('uuid','=',$uuid)->first();
        if (is_object($model) == false) {
            return false;
        }
        $current_value = $model->getAttribute($field_name);
        if ($current_value == 0) {
            $value = 1;
        } else {
            $value = 0;
        }
        $model->setAttribute($field_name,$value);
        $model->update();        
        return $value;
    }
}
