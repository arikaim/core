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
 * Database base condition
*/
abstract class QueryBuilder extends Collection implements QueryBuilderInterface
{   
    const DEFAULT_OPERATOR = '=';
    const DEFAULT_STATEMENT_OPERATOR = 'and';
    
    const AND_OPERATOR = 'and';
    const OR_OPERATOR = 'or';

    public function __construct() 
    {
        parent::__construct();
    }

    public abstract function apply($model);

    public function append($query_builder)
    {   
        if (is_object($query_builder) == false) {
            return false;
        }
        array_push($this->data,$query_builder);
        return true;
    }

    public function build($model)
    {       
        foreach ($this->data as $builder) {            
            $model = $builder->apply($model);
        }
        return $model;
    }
}
