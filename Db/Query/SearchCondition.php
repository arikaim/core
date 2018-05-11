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
    /**
     * Constructor
     *
     * @param array $search 
     */
    public function __construct($search = null) 
    {
        parent::__construct();
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
        var_dump($model);
        exit();
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
        $search_value = $this->getSearchValue();
        if ($search_value == null || empty($search_value) == true) {
            return $model;
        }
        $fields = $this->createSearchFields($model);
        foreach ($fields as $field) {
            switch($field['statement_operator']) {
                case Self::AND_OPERATOR: {
                    $model = $model->where($field['name'],$field['operator'],$search_value);
                    break;
                }
                case Self::OR_OPERATOR: {
                    $model = $model->orWhere($field['name'],$field['operator'],$search_value);
                    break;
                }
                default: {
                    $model = $model->where($field['name'],$field['operator'],$search_value);
                }
            }
        }
        return $model;
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
