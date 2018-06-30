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
 * Find model
*/
trait Find 
{    
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
    
    public function findByColumn($value, $column = "title")
    {
        if ($value == null || empty($value) == true) {
            return false;
        }
        $model = parent::where($column,'=',$value)->first();
        return (is_object($model) == false) ? false : $model;
    }
}
