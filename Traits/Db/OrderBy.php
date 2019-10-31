<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Traits\Db;

/**
 * Order by column trait
*/
trait OrderBy 
{    
    /**
     * Apply order by to current model
     *
     * @param string|null $namespace
     * @return Builder|Model
     */
    public function applyOrderBy($namespace = null)
    {
        return Arikaim\Core\Db\OrderBy::apply($this,$namespace);
    }
}
