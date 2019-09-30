<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Interfaces\Queue;

use Arikaim\Core\Interfaces\Queue\JobInterface;

/**
 * Job recuring interface
 */
interface RecuringJobInterface extends JobInterface
{   
    /**
     * Return recurring interval
     *
     * @return mixed
     */
    public function getRecuringInterval();

    /**
     * Set recurring interval
     *
     * @param mixed $interval
     * @return mixed
     */
    public function setRecuringInterval($interval);

    /**
     * Get next run date time timestamp
     *
     * @return integer
     */
    public function getDueDate();

    /**
     * Return true if job is due
     *
     * @return boolean
     */
    public function isDue();
}
