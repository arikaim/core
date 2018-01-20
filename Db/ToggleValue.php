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

trait ToggleValue 
{       
    public static function toggleValue($uuid, $field_name)
    {        
        $model = parent::where('uuid','=',$uuid)->first();
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
