<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Events;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Number;
use Arikaim\Core\Interfaces\EventInterface;

class Event implements EventInterface
{
   
    protected $parameters = [];
    protected $propagation = false;

    public function __construct($params = []) 
    {
        if (is_array($params) == true) {
            $this->parameters = $params;
        }
    }

    public function stopPropagation()
    {
        $this->propagation = true;
    }

    public function isStopped()
    {
        return $this->propagation;
    }

    public function setParameter($name,$value)
    {
        return $this->parameters[$name] = $value;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getParameter($name) 
    {
        if (isset($this->parameters[$name]) == true) {
            return $this->parameters[$name];
        }
        return null;
    }

    public function hasParameter($name)
    {
        if (isset($this->parameters[$name]) == true) {
            return true;
        }
        return false;
    }
}
