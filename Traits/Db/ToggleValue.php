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
 * Update boolean database values (true or false)
*/
trait ToggleValue 
{       
    /**
     * Toggle model attribute value
     *
     * @param string $field_name
     * @param string|integer|null $id
     * @return boolean
     */
    public function toggle($field_name, $id = null)
    {
        $id = (empty($id) == true) ? $this->id : $id;
    
        $model = $this->findById($id);
        if (is_object($model) == false) {
            return false;
        }
        $value = $model->getAttribute($field_name);
        $value = ($value == 0) ? 1 : 0;
        $result = $model->update([$field_name => $value]);  
        
        return ($result !== false) ? true : false;
    }
}
