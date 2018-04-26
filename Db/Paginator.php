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

/**
 * Create pages for database result
*/
class Paginator 
{    
    public static function getRowsPerPage()
    {
        $rows_per_page = Arikaim::options()->get('paginator.page');
        if ((empty($rows_per_page) == true) || ($rows_per_page == 0)) {
            $rows_per_page = 25;
        }
        return $rows_per_page;
    }

    public static function setRowsPerPage($rows)
    {
        if ((empty($rows) == true) || ($rows < 1)) {
            $rows = 1;
        }
        Arikaim::options()->set('paginator.page',$rows);
    }

    public static function getCurrentPage()
    {
        $page = Arikaim::session()->get('paginator.current.page');
        if ((isset($page) == false) || ($page < 1)) {
            $page = 1;
        }
        return $page;
    }

    public static function setCurrentPage($page)
    {
        if ($page < 1) {
            $page = 1;
        }
        $page = Arikaim::session()->set('paginator.current.page',$page);       
    }

    public static function setOrderBy($field_name,$type)
    {
        Arikaim::session()->set('paginator.order.field',$field_name);    
        Arikaim::session()->set('paginator.order.type',$type);   
    }

    public static function getOrderByField($field_name)
    {
        return Arikaim::session()->get('paginator.order.field');
    }

    public static function getOrderByType($field_name)
    {
        return Arikaim::session()->get('paginator.order.type');
    }

    public static function getPrevPage()
    {
        $page = Self::getCurrentPage() - 1;
        if ($page < 1) {
            $page = 1;
        }
        return $page;
    }

    public static function getNextPage($max_pages)
    {
        $page = Self::getCurrentPage() + 1;
        if ($page > $max_pages) {
            $page = $max_pages;
        }
        return $page;
    }

    public static function create($model)
    {
        $model = $model->paginate(Self::getRowsPerPage(),['*'], 'page',Self::getCurrentPage());
        if (is_object($model) == false) {
            return [];
        }   
        $model = $model->toArray(); 
        $result['paginator']['total'] = $model['total'];
        $result['paginator']['per_page'] = $model['per_page'];
        $result['paginator']['current_page'] = $model['current_page'];
        $result['paginator']['prev_page'] = Self::getPrevPage();
        $result['paginator']['next_page'] = Self::getNextPage($model['last_page']);
        $result['paginator']['last_page'] = $model['last_page'];
        $result['paginator']['from'] = $model['from'];
        $result['paginator']['to'] = $model['to'];
        $result['rows'] = $model['data'];            
        return $result;   
    }
}
