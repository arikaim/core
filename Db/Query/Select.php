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

use Arikaim\Core\Db\Query\QueryBuilder;

/**
 * Database query builder select
*/
class Select extends QueryBuilder
{   
    protected $columns;

    public function __construct($columns) 
    {
        parent::__construct();
        $this->columns = $columns;
        $this->append($this);
    }
    
    public function apply($model)
    {
        if (empty($this->columns) == false) {
            $model = $model->select($this->columns);
        }
        return $model;
    }
}
