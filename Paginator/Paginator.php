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

use Illuminate\Database\Eloquent\Builder;

use Arikaim\Core\Interfaces\Collection\PaginatorInterface;
use Arikaim\Core\Interfaces\Collection\CollectionInterface;

use Arikaim\Core\Paginator\ArrayPaginator;
use Arikaim\Core\Paginator\JsonPaginator;
use Arikaim\Core\Paginator\DbPaginator;
use Arikaim\Core\Paginator\FeedsPaginator;
use Arikaim\Core\Collection\FeedCollection;
use Arikaim\Core\Utils\Utils;

/**
 * Paginator base class
*/
class Paginator implements PaginatorInterface 
{  
    const UNKNOWN = -1;   
    const DEFAULT_PER_PAGE = 25;

    const CARD_VIEW  = 'card';
    const TABLE_VIEW = 'table';
    const GRID_VIEW = 'grid';

    /**
     * Paginator items
     *
     * @var array
    */
    protected $items;

    /**
     * Current page
     *
     * @var integer
    */
    protected $current_page;

    /**
     * Last page
     *
     * @var integer
     */
    protected $last_page;

    /**
     * Row per page value
     *
     * @var integer
     */
    protected $per_page;

    /**
     * Total number of items before slice
     *
     * @var integer
     */
    protected $total;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->current_page = 1;
        $this->last_page = 1;
        $this->items = [];
     
        $this->per_page = Self::DEFAULT_PER_PAGE;
        $this->total = 0;
    }    

    /**
     * Return items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get current page
     *
     * @return integer
     */
    public function getCurrentPage()
    {
        if (empty($this->current_page) == true) {
            return 1;   
        }
        if ($this->last_page != Self::UNKNOWN) {
            return ($this->current_page > $this->last_page) ? $this->last_page : $this->current_page;
        }
        return $this->current_page;
    }

    /**
     * Get first item
     *
     * @return mixed
     */
    public function getFirstItem()
    {
        return (isset($this->items[0]) == true) ? $this->items[0] : null;
    }

    /**
     * Get total items
     *
     * @return integer
     */
    public function getTotalItems()
    {
        return (empty($this->total) == true) ? 0 : $this->total;
    }

    /**
     * Get last item
     *
     * @return mixed
     */
    public function getLastItem()
    {
        return (is_array($this->items) == true) ? end($this->items) : Self::UNKNOWN;
    }

    /**
     * Get last page
     *
     * @return integer
     */
    public function getLastPage()
    {        
        return $this->last_page;
    }

    /**
     * Get rows per page
     *
     * @return integer
     */
    public function getPerPage()
    {
        return $this->per_page;
    }

    /**
     * Return items count
     *
     * @return integer
     */
    public function getItemsCount()
    {
        return count($this->items);
    }

    /**
     * Convert paginator data to array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'paginator' => [
                'current_page' => $this->getCurrentPage(),            
                'last_page'    => $this->getLastPage(),          
                'per_page'     => $this->getPerPage(),                
                'total'        => $this->getTotalItems() 
            ],
            'rows' => $this->getItems()        
        ];
    }

    /**
     * Create paginator
     *
     * @param object|array|json $source   
     * @param integer $page
     * @param integer|null $per_page                         
     * @return PaginatorInterface
     */
    public static function create($source, $page = 1, $per_page = null)
    {       
        if (is_null($source) == true || empty($source) == true) {
            return new Self();
        };
        
        switch($source) {
            case ($source instanceof Builder): {                        
                $paginator = new DbPaginator($source,$page,$per_page);
                break;
            }
            case ($source instanceof FeedCollection): {                        
                $paginator = new FeedsPaginator($source,$page,$per_page);
                break;
            }      
            case ($source instanceof CollectionInterface): {                        
                $paginator = new ArrayPaginator($source->toArray(),$page,$per_page);
                break;
            }                             
            case is_array($source): {
                $paginator = new ArrayPaginator($source,$page,$per_page);
                break;
            }
            case Utils::isJSON($source): {
                $paginator = new JsonPaginator($source,$page,$per_page);
                break;
            }
            default: {
                $paginator = new Self();
            }
        }
        return $paginator;
    }

    /**
     * Slice array items
     *
     * @param array $items
     * @return array
     */
    protected function sliceItems($items)
    {    
        $offset = ($this->current_page - 1) * $this->getPerPage();
        return array_slice($items,$offset,$this->getPerPage());      
    }

    /**
     * Calc last page
     *
     * @return integer
     */
    protected function calcLastPage()
    {
        return max((int)ceil($this->total / $this->per_page), 1);
    }
}
