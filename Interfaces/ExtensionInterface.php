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

interface ExtensionInterface 
{
    public function getRoutes();
    
    public function onAfterInstall();
    public function onBeforeInstall();

    public function onAfterUnInstall();
    public function onBeforeUnInstall();
}
