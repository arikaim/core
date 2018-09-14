<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Interfaces;

interface ModuleInterface
{    
    public function getServiceName();
    public function getTitle();
    public function getDescription();
    public function getVersion();
    public function isBootable();
    public function boot();
}