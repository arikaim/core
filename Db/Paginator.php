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
 * Paginator session helper
*/
class Paginator 
{   
    /**
     * Get rows per page
     *
     * @return void
     */ 
    public static function getRowsPerPage()
    {
        return Arikaim::session()->get('paginator.page',25);
    }

    /**
     * Set rows per page value
     *
     * @param integer $rows
     * @return void
     */
    public static function setRowsPerPage($rows)
    {
        $rows = ($rows < 1) ? 1 : $rows;          
        Arikaim::session()->set('paginator.page',$rows);
    }

    /**
     * Return current page
     *
     * @return integer
     */
    public static function getCurrentPage()
    {
        $page = Arikaim::session()->get('paginator.current.page',1);
        return ($page < 1) ? 1 : $page;        
    }

    /**
     * Set current page
     *
     * @param integer $page
     * @return void
     */
    public static function setCurrentPage($page)
    {
        $page = ($page < 1 || empty($page) == true) ? 1 : $page;
        $page = Arikaim::session()->set('paginator.current.page',$page);       
    }

    /**
     * Get prev page
     *
     * @return integer
     */
    public static function getPrevPage()
    {
        $page = Self::getCurrentPage() - 1;
        return ($page < 1) ? 1 : $page;
    }

    /**
     * Gte next page
     *
     * @param integer $max_pages
     * @return void
     */
    public static function getNextPage($max_pages)
    {
        $page = Self::getCurrentPage() + 1;
        return ($page > $max_pages) ? $max_pages : $page;         
    }

    /** // TODO
     * Create model paginator
     *
     * @param object $model
     * @return array
     */
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

    // TODO  move  to order class
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

}
