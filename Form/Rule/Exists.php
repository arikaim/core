<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Form\Rule;

use Arikaim\Core\Form\AbstractRule;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Arikaim;

class Exists extends AbstractRule
{
    protected $model;
    protected $field_name;
    protected $search_key;

    public function __construct($model_class_name, $field_name, $search_key = null) 
    {
        parent::__construct();
        $this->field_name = $field_name;
        $this->search_key = $search_key;
        $this->model = Model::create($model_class_name);        
    }

    public function customFilter($value) 
    {           
        $data = $this->model->where($this->field_name,'=',$value)->first();
        if (is_object($data) == false) {           
            $this->setError("VALUE_NOT_EXIST_ERROR");
        } 
        return $this->isValid();
    } 

    public function getFilter()
    {       
        return FILTER_CALLBACK;
    }

    public function getFilterOptions()
    {
        return $this->getCustomFilterOptions();
    }
}
