<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Utils;

use \DateInterval;

/**
 * Time intervals
 */
class TimeInterval
{
    /**
     * Interval obj
     *
     * @var object
     */
    private $interval;

    /**
     * Constructor
     *
     * @param string $time
     */
    public function __construct($time = "")
    {
        if (Self::isDurationInverval($time) == true) {
            $this->interval = new DateInterval($time);
        } else {
            $this->interval = DateInterval::createFromDateString($time);
        }
    }
    
    /**
     * Return interval object
     *
     * @return object
     */
    public function getDateInterval()
    {
        return $this->interval;
    }

    /**
     * Get date time interval as string
     *
     * @return string
     */
    public function getInterval()
    {
        $years  = ($this->interval->y > 0) ? $this->interval->y . "Y" : "";
        $months = ($this->interval->m > 0) ? $this->interval->m . "M" : "";
        $days   = ($this->interval->d > 0) ? $this->interval->d . "D" : "";
        $hours  = ($this->interval->h > 0) ? $this->interval->h . "H" : "";
        $minutes = ($this->interval->i > 0) ? $this->interval->i . "M" : "";

        return "P" . $years . $months . $days . "T" . $hours . $minutes;        
    }

    /**
     * Set years
     *
     * @param integer $years
     * @return void
     */
    public function setYears($years)
    {
        $this->interval->y = $years;
    }

    /**
     * Set months
     *
     * @param integer $months
     * @return void
     */
    public function setMonths($months)
    {
        $this->interval->m = $months;
    }

    /**
     * Set days
     *
     * @param integer $days
     * @return void
     */
    public function setDays($days)
    {
        $this->interval->d = $days;
    }

    /**
     * Set hours
     *
     * @param integer $hours
     * @return void
     */
    public function setHours($hours)
    {
        $this->interval->h = $hours;
    }

    /**
     * Set minutes
     *
     * @param integer $minutes
     * @return void
     */
    public function setMinutes($minutes)
    {
        $this->interval->i = $minutes;
    }

    /**
     * Get years
     *
     * @return integer
     */
    public function getYears()
    {
        return $this->interval->y;
    }

    /**
     * Get months
     *
     * @return integer
     */
    public function getMonths()
    {
        return $this->interval->m;
    }

    /**
     * Get hours
     *
     * @return integer
     */
    public function getHours()
    {
        return $this->interval->h;
    }

    /**
     * Get minutes
     *
     * @return integer
     */
    public function getMinutes()
    {
        return $this->interval->i;
    }

    /**
     * Get days
     *
     * @return integer
     */
    public function getDays()
    {
        return $this->interval->d;
    }

    /**
     * Convert interval to array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'years'     => $this->getYears(),
            'months'    => $this->getMonths(),
            'days'      => $this->getDays(),
            'hours'     => $this->getHours(),
            'minutes'   => $this->getMinutes()
        ];
    }

    /**
     * Create interval
     *
     * @param string $interval
     * @return array
     */
    public static function create($interval)
    {       
        $result = new Self($interval);
        
        return $result->toArray();
    }

    /**
     * Return true if text is valid interval string
     *
     * @param string $text
     * @return boolean
     */
    public static function isDurationInverval($text)
    {
        return (substr($text,0,1) == 'P') ? true : false;           
    }
}
