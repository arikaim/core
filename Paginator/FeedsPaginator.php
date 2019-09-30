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
 * Paginate feed collection with unknow last page.
*/
class FeedsPaginator extends Paginator implements PaginatorInterface 
{
    /**
     * Constructor
     *
     * @param FeedCollection $source
     * @param integer $page
     * @param string|integer $per_page_field
     */
    public function __construct($source, $page = 1, $per_page = Paginator::DEFAULT_PER_PAGE)
    {                 
        $this->current_page = $page;
        $this->per_page = $per_page;
        $this->items = $source->fetch($page,$per_page)->getItems();
     
        if (empty($source->getPageKey()) == true) {           
            $this->total = count($this->items);
            $this->items = $this->sliceItems($this->items);
            $this->last_page = $this->calcLastPage();           
        } else {           
            $this->last_page = Self::UNKNOWN;
            $this->total = Self::UNKNOWN;
        }      
    }
}
