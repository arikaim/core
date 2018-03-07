<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Interfaces\Events;

interface EventInterface
{    
    public function setParameter($name,$value);
    public function getParameters();
    public function getParameter($name);
    public function hasParameter($name);
    public function stopPropagation();
    public function isStopped();
}
