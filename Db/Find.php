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
        if (is_numeric($id) == true) {
            return $this->findByColumn($id,'id');
        }
        return $this->findByColumn($id,'uuid'); 
    }
    
    public function findByColumn($value, $column = "title")
    {
        $value = trim($value);       
        $model = parent::where($column,'=',$value)->first();
        return (is_object($model) == false) ? false : $model;
    }
}
