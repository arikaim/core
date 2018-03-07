<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Db;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Condition;
use Arikaim\Core\Errors\Errors;
use Arikaim\Core\Utils\Utils;

class Search 
{    
    private $search;

    public function __construct($search = null) 
    {
        if ($search == null) {
            $this->search = Self::getCurrentSearch();
        }
    }

    public static function getCurrentSearch()
    {
        return Arikaim::session()->get('search');
    }

    public function getSearchConditions($model, $search = null)
    {
        $condition = new Condition();
        if (is_object($model) == false) {
            return $condition->toArray();
        }

        if (is_array($search) == false) {
            $search = Self::getCurrentSearch();
        }
        $search_value = "";
        $search_array = Utils::jsonDecode($search,true);
     
        if (isset($search_array['search']) == true) {
            $search_value = $search_array['search'];
        }
        $fields = $this->parseSearch($model,$search_array);
   
        foreach ($fields as $field) {
            $field['value'] = $search_value;
            $condition->addCondition($field);
        }
        return $condition->toArray();
    }

    public function parseSearch($model,$search)
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

    public function getModelFields($model,array $field)
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
}
