<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Driver;

use Arikaim\Core\Interfaces\DriverInterface;
use Arikaim\Core\Traits\Driver as DriverTrait;

/**
 * Driver base class
*/
class Driver implements DriverInterface
{
    use DriverTrait;

    /**
     * Constructor
     */
    public function __construct()
    {
    }
}
