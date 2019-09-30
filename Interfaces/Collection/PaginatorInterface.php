<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Interfaces\Collection;

/**
 * Paginator interface
 */
interface PaginatorInterface
{    
    /**
     * Return paginator items 
     *
     * @return mixed
    */
    public function getItems();

    /**
     * Return current page
     *
     * @return integer
     */
    public function getCurrentPage();

    /**
     * Return first item
     *
     * @return mixed
     */
    public function getFirstItem();
}
