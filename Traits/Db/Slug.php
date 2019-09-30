<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Traits\Db;

use Arikaim\Core\Utils\Utils;

/**
 * Create slug
*/
trait Slug 
{    
    /**
     * Set model event on saving
     *
     * @return void
     */
    public static function bootSlug()
    {
        static::saving(function($model) {   
            $model = Self::createSlug($model);
        });        
    }

    /**
     * Get slug attribute name
     *
     * @return string
     */
    public function getSlugAttributeName()
    {
        return (isset($this->slug_attribute) == true) ? $this->slug_attribute : 'slug';
    }

    /**
     * Get slug source attribute name
     *
     * @return string
     */
    public function getSlugSourceAttributeName()
    {
        return (isset($this->slug_source) == true) ? $this->slug_source : 'title';
    }

    /**
     * Get slug separator
     *
     * @return void
     */
    public function getSlugSeparator()
    {
        return (isset($this->slug_separator) == true) ? $this->slug_separator : '-';
    }

    /**
     * Create slug
     *
     * @param string $text
     * @param string $options
     * @return string
     */
    public static function createSlug($model)
    {
        $slug_attribute = $model->getSlugAttributeName();
    
        if (empty($model->attributes[$slug_attribute]) == true) {
            $slug_source = $model->getSlugSourceAttributeName();
            $separator = $model->getSlugSeparator();        
            if (is_null($model->$slug_source) == false) {                   
                $model->attributes[$slug_attribute] = Utils::slug($model->$slug_source,$separator);
            }              
        }
        
        return $model;
    }

    /**
     * Find model by slug
     *
     * @param string $slug
     * @return Model
     */
    public function findBySlug($slug)
    {
        $slug_attribute = $this->getSlugAttributeName();
        $model = $this->where($slug_attribute,'=',$slug)->first();

        return (is_object($model) == true) ? $model : false;
    }
}
