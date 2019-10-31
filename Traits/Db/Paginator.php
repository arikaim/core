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
 * Paginator trait
*/
trait Paginator 
{    
    /**
     * Create paginator for current model
     *
     * @param string|null $namespace
     * @return Builder|Model
     */
    public function createPaginator($namespace = null)
    {
        return Arikaim\Core\Paginator\SessionPaginator::create($this,$namespace);
    }
}
