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

class Unique extends AbstractRule
{
    protected $model;
    protected $field_name;
    protected $except_value;

    public function __construct($model_class_name, $field_name, $extension_name = null, $except_value = null, $error_code = "VALUE_EXIST_ERROR") 
    {
        parent::__construct(null,null,$error_code);
        $this->field_name = $field_name;
        $this->except_value = $except_value;
        $this->model = Model::create($model_class_name,$extension_name);        
    }

    public function customFilter($value) 
    {           
        $model = $this->model->where($this->field_name,'=',$value);
        if ($this->except_value != null) {
            $model = $model->where($this->field_name,'<>',$this->except_value);
        }
        $data = $model->first();
        if (is_object($data) == true) {           
            $this->setError();
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
