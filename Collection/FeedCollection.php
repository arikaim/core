<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Collection;

use Arikaim\Core\Interfaces\Collection\CollectionInterface;
use Arikaim\Core\Interfaces\Collection\FeedsInterface;

use Arikaim\Core\System\Url;
use Arikaim\Core\Utils\Arrays;

/**
 * Feed Collection class
 */
class FeedCollection extends Collection implements CollectionInterface, FeedsInterface, \Countable, \ArrayAccess, \IteratorAggregate
{
    /**
     * Item key mappings
     *
     * @var array
     */
    protected $key_maps = [];

    /**
     * Feed base url
     *
     * @var string
     */
    protected $base_url;

    /**
     * Url params
     *
     * @var array|string
     */
    protected $params;

    /**
     * Feed items key
     *
     * @var string|null
     */
    protected $items_key;

    /**
     * Array key in params 
     *
     * @var string
     */
    protected $page_key;

    /**
     * Items per page array key
     *
     * @var string|null
     */
    protected $per_page_key;

    /**
     * Constructor
     *
     *  @param string|null $base_url
     *  @param array|string $params
     *  @param string|null $items_key
     *  @param string|null $page_key
     *  @param string|null $per_page_key
     */
    public function __construct($base_url = null, $params = [], $items_key = null, $page_key = null, $per_page_key = null) 
    {  
        $this->base_url = $base_url;
        $this->params = $params;
        $this->items_key = $items_key;
        $this->page_key = $page_key;
        $this->per_page_key = $per_page_key;

        parent::__construct([]);
    }

    /**
     * Set page array key for params array 
     *
     * @param string $key
     * @return FeedCollection
     */
    public function pageKey($key)
    {
        $this->page_key = $key;
        return $this;
    }

    /**
     * Get pake key 
     *
     * @return integer
     */
    public function getPageKey()
    {
        return $this->page_key;
    }

    /**
     * Set per page array key for params array 
     *
     * @param string $key
     * @return FeedCollection
     */
    public function perPageKey($key)
    {
        $this->per_page_key = $key;
        return $this;
    }

    /**
     * Set feed base url
     *
     * @param string $url
     * @return FeedCollection
     */
    public function baseUrl($url)
    {
        $this->base_url = $url;
        return $this;
    }

    /**
     * Set params
     *
     * @param array|string $params
     * @return FeedCollection
     */
    public function params($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Set items key
     *
     * @param string|null $key
     * @return FeedCollection
     */
    public function itemsKey($key)
    {
        $this->items_key = $key;
        return $this;
    }

    /**
     * Fetch feed
     *
     * @return FeedCollection
     */
    public function fetch($page = null, $per_page = null)
    {
        $this->setPage($page);
        $this->setPerPage($per_page);

        $url = $this->getUrl();
        $json = Url::fetch($url);
        $data = json_decode($json,true);
        if (is_array($data) == true) {
            $this->data = $data;
        }      
        return $this;
    }

    /**
     * Set feed current page
     *
     * @param integer $page
     * @return void
     */
    public function setPage($page)
    {
        if (empty($this->page_key) == false) {
            $this->params[$this->page_key] = $page;
        }       
    }

    /**
     * Set feed items per page
     *
     * @param integer $per_page
     * @return void
     */
    public function setPerPage($per_page)
    {
        if (empty($this->per_page_key) == false) {
            $this->params[$this->per_page_key] = $per_page;
        }       
    }

    /**
     * Get full url
     *
     * @return string
     */
    public function getUrl()
    {
        if (is_string($this->params) == true) {
            $query_string = $this->params;
        }
        if (is_array($this->params) == true) {
            if (Arrays::isAssociative($this->params) == true) {
                $query_string = "?" . http_build_query($this->params);
            } else {
                $query_string = "";
                foreach ($this->params as $value) {
                    $query_string .= $value . "/";
                }
            }           
        }     
        return $this->base_url . $query_string;
    }

    /**
     * Get base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->base_url;
    }

    /**
     * Get items key
     *
     * @return string|null
     */
    public function getItemsKey()
    {
        return $this->items_key;
    }

    /**
     * Get url params
     *
     * @return array|string
     */
    public function getUrlParams()
    {   
        return $this->params;
    }

    /**
     * Return feed items array
     *
     * @return array|null
     */
    public function getItems($apply_key_maps = true)
    {
        $items = $this->getItemsArray();
        return ($apply_key_maps == true) ? $this->applyKeyMaps($items) : $items;
    }

    /**
     * Get items array
     *
     * @return array
     */
    protected function getItemsArray()
    {
        if (empty($this->items_key) == true) {
            $items = $this->data;
        } else {
            $items = (isset($this->data[$this->items_key]) == true) ? $this->data[$this->items_key] : null;
        }
        return $items;
    } 

    /**
     * Get feed item
     *
     * @param integer $index
     * @return mixed
     */
    public function getItem($index, $apply_key_maps = true)
    {
        $items = $this->getItemsArray();
        $item = (isset($items[$index]) == true) ? $items[$index] : [];
        
        return ($apply_key_maps == true) ? $this->transformItem($item) : $item;           
    }

    /**
     * Set key maps
     *
     * @param array $key_maps
     * @return void
     */
    public function setKeyMaps($key_maps)
    {
        $this->key_maps = $key_maps;
    }

    /**
     * Change array key 
     *
     * @param string $key
     * @param string $map_to
     * @return FeedCollection
     */
    public function mapKey($key, $map_to)
    {
        $this->key_maps[$key] = $map_to;
        return $this;
    }

    /**
     * Change item array keys
     *
     * @param array $items
     * @return array
     */
    public function applyKeyMaps($items = null)
    {
        $items = (empty($items) == true) ? $this->data : $items;
 
        foreach ($items as $key => $item) {                    
            $items[$key] = $this->transformItem($item);                       
        }
        return $items;
    }

    /**
     * Transform item
     *
     * @param array $item
     * @return array
     */
    protected function transformItem($item)
    {
        foreach ($this->key_maps as $key => $value) {
            if (is_callable($value) == true) {          
                $callback = function() use($value,$item) {
                    return $value($item);
                };        
                $item[$key] = $value($item);
                continue;
            }
            if (isset($item[$value]) == true) {
                $item[$key] = $item[$value];
                unset($item[$value]);   
                continue;
            }
        }
        return $item;      
    }
}
