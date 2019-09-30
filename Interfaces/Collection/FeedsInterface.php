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
 * Feeds Collection interface
 */
interface FeedsInterface
{    
    /**
     * Fetch feed
     *
     * @param integer|null $page 
     * @param integer|null $per_page
     * @return boolean
     */
    public function fetch($page = null, $per_page = null);

    /**
     * Get feed item
     *
     * @param integer $index
     * @return mixed
     */
    public function getItem($index);

    /**
     * Return feed items array
     *
     * @return array|null
     */
    public function getItems();

    /**
     * Get items key
     *
     * @return string|null
     */
    public function getItemsKey();

    /**
     * Get base url
     *
     * @return string
     */
    public function getBaseUrl();

    /**
     * Get full url
     *
     * @return string
     */
    public function getUrl();
}
