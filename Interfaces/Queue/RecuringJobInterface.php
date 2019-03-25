<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/

interface RecuringJobInterface
{   
    /**
     * Return recurring interval
     *
     * @return string
     */
    public function getRecuringInterval();
}
