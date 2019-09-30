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
 * Paginate Illuminate\Database\Eloquent\Builder objects
*/
class DbPaginator extends Paginator implements PaginatorInterface 
{
    /**
     * Constructor
     *
     * @param Builder $builder 
     * @param integer $page
     * @param integer $per_page
     */
    public function __construct($builder, $page, $per_page = Paginator::DEFAULT_PER_PAGE)
    {          
        $this->total = $builder->toBase()->getCountForPagination();
        $this->items = $this->total ? $builder->forPage($page, $per_page)->get(['*']) : [];
     
        $this->current_page = $page;
        $this->per_page = $per_page;
       
        $this->last_page = $this->calcLastPage();
    }
}
