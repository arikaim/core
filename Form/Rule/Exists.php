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
 * Check if value exists in database table
 */
class Exists extends AbstractRule
{
    protected $model;
    protected $field_name;
   
    /**
     * Constructor
     *
     * @param string $model_class_name Db model class name
     * @param string $field_name Db field name
     * @param string|null $extension_name Extension name
     * @param string $error
     */
    public function __construct($model_class_name, $field_name, $extension_name = null, $error = "VALUE_NOT_EXIST_ERROR") 
    {
        parent::__construct(null,null,$error);
        $this->field_name = $field_name;
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
        $data = $this->model->where($this->field_name,'=',$value)->first();
        return (is_object($data) == false) ? false : true;                   
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
