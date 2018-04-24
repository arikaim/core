<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Db;

use Arikaim\Core\Utils\Collection;

/**
 * Database order by
*/
class OrderBy extends Collection
{   
    const ASC = 'asc';
    const DESC = 'desc';
    
    public function __construct($field_name, $type = Self::ASC) 
    {
        parent::__construct();
        $this->additem($field_name,$type);
    }

    public function addItem($field_name, $type = Self::ASC)
    {
        $order['field'] = $field_name;
        $order['type'] = $type;
        array_push($this->data,$order);
        return true;
    }

    public function append($order)
    {
        if ($order instanceof OrderBy) {
            $order = $order->toArray();
        }
       
        if (is_array($order) == false) {
            return false;
        }
        $this->data = array_merge($this->data,$order);
        return true;
    }

    public function apply($model)
    {       
        foreach ($this->data as $order) {    
            $order = $this->normalize($order);
            $model = $model->orderBy($order['field'],$order['type']);
        }
        return $model;
    }

    private function normalize($order_by)
    {
        if (isset($order_by['field']) == false) {
            return false;
        }
        if (isset($order_by['type']) == false) {
            $order_by['type'] = Self::ASC;
        }
        return $order_by;
    }
}
