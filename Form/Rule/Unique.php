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

/**
 * Unique value rule, Check if value in model table not exists
 */
class Unique extends AbstractRule
{
    protected $model;
    protected $field_name;
    protected $except_value;

    /**
     * Constructor
     *
     * @param string $model_class_name Db Model class name
     * @param string $field_name Field name
     * @param string $extension_name
     * @param mixed $except_value
     * @param string $error
     */
    public function __construct($model_class_name, $field_name, $extension_name = null, $except_value = null, $error = "VALUE_EXIST_ERROR") 
    {
        parent::__construct(null,null,$error);
        $this->field_name = $field_name;
        $this->except_value = $except_value;
        $this->model = Model::create($model_class_name,$extension_name);        
    }

    /**
     * Validate value
     *
     * @param mixed $value
     * @return boolean
     */
    public function customFilter($value) 
    {           
        $model = $this->model->where($this->field_name,'=',$value);
        if ($this->except_value != null) {
            $model = $model->where($this->field_name,'<>',$this->except_value);
        }
        $data = $model->first();
        return (is_object($data) == true) ? false : true;          
    } 

    /**
     * Return filter type
     *
     * @return int
     */
    public function getFilter()
    {       
        return FILTER_CALLBACK;
    }

    /**
     * Return filter options
     *
     * @return array
     */
    public function getFilterOptions()
    {
        return $this->getCustomFilterOptions();
    }
}
