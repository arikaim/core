<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Extension;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\Collection;

class Events extends Collection
{
    public function add($name,$handler_class,$handler_menthod = "",$priority = 0)
    {
        $event['name'] = $name;
        $event['priority'] = $priority;
        $event['handler_class'] = $handler_class;
        $event['handler_method'] = $handler_menthod;

        if ($this->isValid($event) == true) {
            array_push($this->data,$event);
            return true;
        }       
        return false;
    }

    private function isValid($event) 
    {
        if (isset($event['name']) == false) return false;
        if (isset($event['handler_class']) == false) return false;
        return true;
    }
}
