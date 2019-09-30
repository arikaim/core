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
use Arikaim\Core\Paginator\ArrayPaginator;

/**
 * Paginate Josn
*/
class JsonPaginator extends ArrayPaginator implements PaginatorInterface 
{
    /**
     * Constructor
     *
     * @param string $json_text
     * @param integer $page
     * @param integer $per_page
     */
    public function __construct($json_text, $page, $per_page = Paginator::DEFAULT_PER_PAGE)
    {      
        $items = json_decode($json_text,true);
        parent::__construct($items,$page,$per_page);       
    }
}
