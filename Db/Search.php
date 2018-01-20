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

//use Illuminate\Database\Capsule\Manager;
use Arikaim\Core\Arikaim;

class Search 
{    
   
    public static function setSearch($text,array $search_fields = [])
    {
        Arikaim::session()->set('paginator.search.text',$text);
        Arikaim::session()->set('paginator.search.fields',$search_fields);
    }

    public static function getCurrentSearch()
    {
        return Arikaim::session()->get('search');
    }

    public static function search($model,$search = null)
    {
        if (is_array($search) == false) {
            $search = Self::getCurrentSearch();
        }

        if (empty($search['value']) == true) {
            return $model;
        }
        if (is_object($model) == false) {
            return $model;
        }
        if (is_array($search) == false) {
            return $model;
        }
        $model = $model->find(1);
        $fields = $model->getAttributes();
    
        if (isset($search['fields']) == true) {
            if (is_array($search['fields']) == false) {
               $fields = $search['fields'];
            }           
        }

        foreach ($fields as $field => $value) {           
            $model = $model->orWhere($field,'LIKE',$search['value']);
        }
        return $model;
    }
}
