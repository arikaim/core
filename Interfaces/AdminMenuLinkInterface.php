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

interface AdminMenuLinkInterface 
{
    public function getLinkTitle();
    public function getLinkIcon();
    public function getLinkSubtitle();
    public function getLinkComponentName();
    public function getLinkPosition();
}
