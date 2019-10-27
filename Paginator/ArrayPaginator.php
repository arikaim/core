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

use Arikaim\Core\Interfaces\Collection\PaginatorInterface;
use Arikaim\Core\Paginator\Paginator;

/**
 * Paginate array
*/
class ArrayPaginator extends Paginator implements PaginatorInterface 
{
    /**
     * Constructor
     *
     * @param array $items
     * @param integer $page
     * @param integer $perPage
     */
    public function __construct($items, $page, $perPage = Paginator::DEFAULT_PER_PAGE)
    {      
        $this->currentPage = $page;
        $this->perPage = $perPage;
        $this->total = count($items);
        $this->lastPage = $this->calcLastPage();
        $this->items = $this->sliceItems($items);       
    }
}
