<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Paginator;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Paginator\Paginator;

/**
 * Paginator session helper
*/
class SessionPaginator 
{   
    /**
     * Return view type
     *
     * @param string|null $namespace
     * @param string $default
     * @return string
     */
    public static function getViewType($namespace = null, $default = Paginator::TABLE_VIEW)
    {
        return Arikaim::session()->get(Utils::createKey('paginator.view.type',$namespace),$default);
    }

    /**
     * Set view type
     *
     * @param string|null $namespace
     * @param string $type
     * @return void
     */
    public static function setViewType($type, $namespace = null)
    {
        $type = (empty($type) == true) ? Paginator::TABLE_VIEW : $type;
        return Arikaim::session()->set(Utils::createKey('paginator.view.type',$namespace),$type);
    }

    /**
     * Get rows per page
     *
     * @param string|null $namespace
     * @return void
     */ 
    public static function getRowsPerPage($namespace = null)
    {
        return Arikaim::session()->get(Utils::createKey('paginator.page',$namespace),Paginator::DEFAULT_PER_PAGE);
    }

    /**
     * Set rows per page value
     *
     * @param string|null $namespace
     * @param integer $rows
     * @return void
     */
    public static function setRowsPerPage($rows, $namespace = null)
    {
        $rows = ($rows < 1) ? 1 : $rows;          
        Arikaim::session()->set(Utils::createKey('paginator.page',$namespace),$rows);
    }

    /**
     * Return current page
     *
     * @param string|null $namespace
     * @return integer
     */
    public static function getCurrentPage($namespace = null)
    {
        $page = (integer)Arikaim::session()->get(Utils::createKey('paginator.current.page',$namespace),1);     
        return ($page < 1) ? 1 : $page;        
    }

    /**
     * Set current page
     *
     * @param string|null $namespace
     * @param integer $page
     * @return void
     */
    public static function setCurrentPage($page, $namespace = null)
    {
        $page = ($page < 1 || empty($page) == true) ? 1 : $page;       
        Arikaim::session()->set(Utils::createKey('paginator.current.page',$namespace),$page);       
    }

    /**
     * Create paginator
     *
     * @param string|null $namespace
     * @param integer|null $page_size
     * @param object|array|json $source                            
     * @return array
     */
    public static function create($source, $namespace = null, $page_size = null, $current_page = null)
    {            
        $page_size = (empty($page_size) == true) ? Self::getRowsPerPage($namespace) : $page_size;
        $current_page = (empty($current_page) == true) ? Self::getCurrentPage($namespace) : $current_page;

        $paginator = Paginator::create($source,$current_page,$page_size);
        $data = $paginator->toArray();

        if ($paginator->getItemsCount() == 0 && $current_page > 1) {
            Self::setCurrentPage(1,$namespace);
            $paginator = Paginator::create($source,1,$page_size);
            $data = $paginator->toArray();           
        }
        Self::savePaginator($namespace,$data['paginator']);
        
        return $paginator;
    }

    /**
     * Save paginator array in session
     *
     * @param string|null $namespace
     * @param array $data
     * @return void
     */
    public static function savePaginator($namespace, $data)
    {
        Arikaim::session()->set(Utils::createKey('paginator',$namespace),$data);          
    } 

    /**
     * Read paginator data from session
     *
     * @param string|null $namespace
     * @return array
     */
    public static function getPaginator($namespace)
    {
        $paginator = Arikaim::session()->get(Utils::createKey('paginator',$namespace),[]);  
        $paginator['current_page'] = Self::getCurrentPage($namespace);
     
        return $paginator;    
    } 

    /**
     * Clear paginator data
     *
     * @param string $namespace
     * @return void
     */
    public static function clearPaginator($namespace)
    {
        Arikaim::session()->remove(Utils::createKey('paginator',$namespace)); 
        Self::setCurrentPage(1,$namespace); 
    }
}
