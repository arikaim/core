<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Db;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Utils;

/**
 * Order by 
*/
class OrderBy 
{   
    /**
     * Undocumented function
     *
     * @param string $fieldName
     * @param string $type (asc|desc)
     * @param string|null $namespace
     * @return bool
     */
    public static function setOrderBy($fieldName, $type = null, $namespace = null)
    {
        if (empty($fieldName) == true) {
            return false;
        }
        $type = (empty($type) == true) ? 'asc' : $type;
        Arikaim::session()->set(Utils::createKey('order.by',$namespace),[$fieldName => $type]);         
        return true; 
    }

    /**
     * Return order by
     *
     * @param string:null $namespace
     * @return mixed
     */
    public static function getOrderBy($namespace = null)
    {
        return Arikaim::session()->get(Utils::createKey('order.by',$namespace),[]);
    }

    /**
     * Delete order by column
     *
     * @param string:null $namespace
     * @return void
     */
    public static function deleteOrderBy($namespace = null)
    {
        return Arikaim::session()->remove(Utils::createKey('order.by',$namespace));
    }

    /**
     * Apply order by to model
     *
     * @param Model|Builder $builder
     * @param string $namespace
     * @return Model|Builder
     */
    public static function apply($builder, $namespace = null)
    {
        $order = Self::getOrderBy($namespace);
        
        $field = key($order);
        $type = (isset($order[$field]) == true) ? $order[$field] : 'asc';
       
        if (empty($field) == false) {
           $builder = $builder->orderBy($field,$type);
        }

        return $builder;
    }
}
