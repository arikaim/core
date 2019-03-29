<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Validator\Rule;

use Arikaim\Core\Validator\Rule;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Arikaim;

/**
 * Check if value exists in database table
 */
class Exists extends Rule
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
        return (bool)$this->model->where($this->field_name,'=',$value)->exists();
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
