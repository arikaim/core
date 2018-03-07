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
    public function getModuleTitle();
    public function getModuleDescription();
    public function getModuleVersion();
    public function isBootable();
}