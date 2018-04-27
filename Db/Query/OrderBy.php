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

/**
 * Database order by
*/
class OrderBy extends QueryBuilder
{   
    const ASC = 'asc';
    const DESC = 'desc';
    
    protected $field;
    protected $type;
    
    public function __construct($field_name, $type = Self::ASC) 
    {
        parent::__construct();
        $this->field = $field_name;
        $this->type = $type;
        $this->append($this);
    }

    public function apply($model)
    { 
        $valid = $this->validate();
        if ($valid == false) {
            return $model;
        }
        $model = $model->orderBy($this->field,$this->type);
        return $model;
    }

    protected function validate()
    {
        if (isset($this->field) == false) {
            return false;
        }
        if (isset($this->type) == false) {
            $this->type = Self::ASC;
        }
        return true;
    }
}
