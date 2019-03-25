<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Interfaces\Packages;

interface PackageInterface 
{  
    public function getName();
    public function getProperties($full = false);
    public function install();
    public function unInstall();
    public function reInstall();
    public function enable();
    public function disable();
}
