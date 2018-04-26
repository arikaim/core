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
 * Database order by
*/
class OrderBy extends QueryBuilder implements QueryBuilderInterface
{   
    const ASC = 'asc';
    const DESC = 'desc';
    
    public function __construct($field_name, $type = Self::ASC) 
    {
        parent::__construct();
        if (is_array($field_name) == true) {
            $this->data = $field_name;
        }
        $this->additem($field_name,$type);
    }

    public function addItem($field_name, $type = Self::ASC)
    {
        $order['field'] = $field_name;
        $order['type'] = $type;
        array_push($this->data,$order);
        return true;
    }

    public function apply($model, $data)
    { 
        $data = $this->normalize($data);
        $model = $model->orderBy($data['field'],$data['type']);
        return $model;
    }

    public function build($model)
    {       
        foreach ($this->data as $data) {  
            $model = $this->apply($model,$data);
        }
        return $model;
    }

    private function normalize($data)
    {
        if (isset($data['field']) == false) {
            return false;
        }
        if (isset($data['type']) == false) {
            $data['type'] = Self::ASC;
        }
        return $data;
    }
}
