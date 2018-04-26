<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Db\Query;

use Arikaim\Core\Utils\Collection;
use Arikaim\Core\Interfaces\QueryBuilderInterface;

/**
 * Database query builder select
*/
class Select extends Collection implements QueryBuilderInterface
{   
    public function __construct(array $columns) 
    {
        parent::__construct();
        $this->data = $fields;
    }

    public function addItem($field_name)
    {       
        array_push($this->data,$field_name);
        return true;
    }

    public function append($select)
    {
        if ($select instanceof Select) {
            $select = $select->toArray();
        }
       
        if (is_array($order) == false) {
            return false;
        }
        $this->data = array_merge($this->data,$select);
        return true;
    }

    public function build($model)
    {       
        foreach ($this->data as $columns) {    
            $this->apply($model,$columns);
        }
        return $model;
    }
    
    public function apply($model,$data)
    {
        if (is_array($columns) == true) {
            $model = $model->select($data);
        }
        return $model;
    }
}
