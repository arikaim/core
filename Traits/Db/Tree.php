<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Traits\Db;

/**
 * Manage models with parent - child relations, parent_id field is required in model 
*/
trait Tree 
{       
    public function getModelPath($model, $parent_field_name = "parent_id")
    {
        $result = [];
        array_unshift($result,$model->toArray());
        while ($model != false) {
            $parent_id = $model->{$parent_field_name};
            $model = parent::where('id','=',$parent_id)->first();
            if (is_object($model) == true) {
                array_unshift($result,$model->toArray());
            }
        }
        return $result;
    }

    public function getTreePath($id, $parent_field_name = "parent_id")
    {
        $model = parent::where('id','=',$id)->first();
        if (is_object($model) == false) {
            return false;
        }
        return $this->getModelPath($model,$parent_field_name);
    }
}
