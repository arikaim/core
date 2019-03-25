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
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Utils;

/**
 * Database search condition
*/
class SearchCondition extends QueryBuilder
{

    protected $search;
    protected $fields;

    /**
     * Constructor
     *
     * @param array $search 
     */
    public function __construct($search = null,array $fields = ['all']) 
    {
        parent::__construct();
        $this->fields = $fields;
        $this->search = ($search != null) ? $search : Self::getCurrentSearch();
    }

    /**
     * Return current search text
     *
     * @return mixed
     */
    public static function getCurrentSearch()
    {
        $search_json = Arikaim::session()->get('search');
        return Utils::jsonDecode($search_json,true);
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
        $result = [];
        $model = $model->first();       
        $fields = $model->getAttributes();
        if (is_array($fields) == false) {
            return $result;
        }
        foreach ($fields as $key => $item) {
            $condition['name'] = $key;
            $condition['operator'] = $field['operator'];
            $condition['statement_operator'] = $field['statement_operator'];
            array_push($result,$condition);
        }
        return $result;
    }

    /**
     * Create search fields array
     *
     * @param object $model
     * @param mixed $search
     * @return array
     */
    private function createSearchFields($model)
    {
        $fields = $this->getSearchFeilds();
        if (array_search('all',$this->fields) !== false) {
            $fields = $this->getModelFields($model,$fields);
            return $fields;
        }
        $result = [];
        foreach ($this->fields as $feild_name) {
            $field['name'] = $feild_name;
            $field['operator'] = $fields['operator'];
            $field['statement_operator'] = $fields['statement_operator'];
            array_push($result,$field);
        }
        return $result;
    }

    /**
     * Return model object
     *
     * @param object $model
     * @return object
     */
    public function apply($model)
    {
        $search_value = $this->getSearchValue();
        if (empty($search_value) == true) {
            return $model;
        }
        $fields = $this->createSearchFields($model);
        foreach ($fields as $field) {
            $field = $this->validate($field,$search_value);
            switch($field['statement_operator']) {
                case Self::AND_OPERATOR: {
                    $model = $model->where($field['name'],$field['operator'],$field['value']);
                    break;
                }
                case Self::OR_OPERATOR: {
                    $model = $model->orWhere($field['name'],$field['operator'],$field['value']);
                    break;
                }
                default: {
                    $model = $model->where($field['name'],$field['operator'],$field['value']);
                }
            }
        }
        return $model;
    }

    protected function validate($field,$search_value)
    {
        if (isset($field['name']) == false) {
            return false;
        }
        if (isset($field['statement_operator']) == false) {
            $field['statement_operator'] = Self::DEFAULT_STATEMENT_OPERATOR;
        }
        if (isset($field['operator']) == false) {
            $field['operator'] = Self::DEFAULT_OPERATOR;
        }        
        if (strtolower($field['operator']) == 'like') {
            $field['value'] = "%" . $search_value . "%";
        } else {
            $field['value'] = $search_value;
        }
        return $field;
    }

    public function getSearchFeilds()
    {
        if (isset($this->search['fields']) == false) {
            return [];
        }
        return $this->search['fields'];
    }

    public function getSearchValue()
    {
        if (isset($this->search['search']) == true) {
            return $this->search['search'];
        }
        return null;
    }
}
