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
use Arikaim\Core\Db\Query\Condition;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Utils;

/**
 * Database search condition
*/
class SearchCondition extends QueryBuilder
{
    /**
     * Constructor
     *
     * @param string $model_class_name
     * @param string $extension_name
     * @param string $search
     */
    public function __construct($model_class_name, $extension_name = null, $search = null) 
    {
        parent::__construct();
        $this->createSearchConditions($model_class_name,$extension_name,$search);
    }

    /**
     * Return current search text
     *
     * @return mixed
     */
    public static function getCurrentSearch()
    {
        return Arikaim::session()->get('search');
    }

    /**
     * Return all model fields
     *
     * @param object $model
     * @param array $field
     * @return array
     */
    public function getModelFields($model, array $field)
    {
        $model = $model->find(1);
        $fields = $model->getAttributes();
        if (is_array($fields) == false) {
            return [];
        }
        $result = [];
        foreach ($fields as $key => $item) {
            $condition['field'] = $key;
            $condition['operator'] = $field['operator'];
            $condition['statement_operator'] = $field['statement_operator'];
            array_push($result,$condition);
        }
        return $result;
    }

    /**
     * Create condition for every filed in model 
     *
     * @param string $model_class_name
     * @param string $extension_name
     * @param string $search
     * @return boolean
     */
    private function createSearchConditions($model_class_name, $extension_name = null, $search = null)
    {
        $model = Model::create($model_class_name,$extension_name);
        if (is_object($model) == false) {
            return false;
        }

        if (is_array($search) == false) {
            $search = Self::getCurrentSearch();
        }
        $search_value = "";
        $search_array = Utils::jsonDecode($search,true);
     
        if (isset($search_array['search']) == true) {
            $search_value = $search_array['search'];
        }
        $fields = $this->createSearchFields($model,$search_array);
        foreach ($fields as $condition) {
            $condition['value'] = $search_value;
            $condition = new Condition($condition['field'],$condition['operator'],$condition['value'],$condition['statement_operator']);
            $this->append($condition);
        }
        return true;
    }

    /**
     * Create search fields array
     *
     * @param object $model
     * @param mixed $search
     * @return array
     */
    private function createSearchFields($model, $search)
    {
        if (isset($search['fields']) == false) {
            return [];
        }
        $fields = $search['fields'];
        if (array_search('all',$fields) != false) {
            $fields = $this->getModelFields($model,$fields);
        } 
        return $fields;
    }

    /**
     * Return model object
     *
     * @param object $model
     * @return object
     */
    public function apply($model)
    {
        return $model;
    }
}
