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
 * Search trait
*/
trait Search 
{    
    /**
     * Apply search conditions for current model
     *
     * @param string|null $namespace
     * @return Builder|Model
     */
    public function applySearch($namespace = null)
    {
        return Arikaim\Core\Db\Search::apply($this,$namespace);
    }
}
