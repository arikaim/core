<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Traits\Db;

use Arikaim\Core\Utils\Utils;
use Cocur\Slugify\Slugify;

/**
 * Update UUID field
*/
trait Slug 
{    
    protected $slug_attribute = 'slug';
    protected $slug_source = 'title';
    protected $slug_options = [];

    public static function bootSlug()
    {
        static::saving(function($model) {   
            $model->attributes[$model->slug_attribute] = Self::createSlug($model->attributes[$model->slug_source],$model->slug_options);
        });
    }

    public static function createSlug($text, $options = [])
    {
        $slug = new Slugify($options);
        return $slug->slugify($text);
    }
}
