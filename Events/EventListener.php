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

abstract class EventListener implements EventInterface
{
    protected $name;
    protected $priority;
    protected $title;
    protected $description;

    public function __construct($name, $title = "", $priority = 0, $description = "") 
    {
        $this->name = $name;
        $this->priority = Number::getNumericValue($priority);
        $this->title = $title;
        $this->description = $description;
    }

    public function getClassName()
    {
        return get_class($this);
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getTitle()
    {
        return $this->title;
    } 

    public function getName()
    {
        return $this->name;
    } 

    public function execute($args = [])
    {
    }
}
