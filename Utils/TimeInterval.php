<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Utils;

use Arikaim\Core\Arikaim;
use \DateInterval;

class TimeInterval
{
    private $interval;

    public function __construct($time = "")
    {
        if (Self::isDurationInverval($time) == true) {
            $this->interval = new DateInterval($time);
        } else {
            $this->interval = DateInterval::createFromDateString($time);
        }
    }
    
    public function getDateInterval()
    {
        return $this->interval;
    }

    public function getInterval()
    {
        $years  = ($this->interval->y > 0) ? $this->interval->y . "Y" : "";
        $months = ($this->interval->m > 0) ? $this->interval->m . "M" : "";
        $days   = ($this->interval->d > 0) ? $this->interval->d . "D" : "";
        $hours  = ($this->interval->h > 0) ? $this->interval->h . "H" : "";
        $minutes = ($this->interval->i > 0) ? $this->interval->i . "M" : "";

        $interval = "P" . $years . $months . $days . "T" . $hours . $minutes;
        return $interval;
    }

    public function setYears($years)
    {
        $this->interval->y = $years;
    }

    public function setMonths($months)
    {
        $this->interval->m = $months;
    }

    public function setDays($days)
    {
        $this->interval->d = $days;
    }

    public function setHours($hours)
    {
        $this->interval->h = $hours;
    }

    public function setMinutes($minutes)
    {
        $this->interval->i = $minutes;
    }

    public function getYears()
    {
        return $this->interval->y;
    }

    public function getMonths()
    {
        return $this->interval->m;
    }

    public function getHours()
    {
        return $this->interval->h;
    }

    public function getMinutes()
    {
        return $this->interval->i;
    }

    public function getDays()
    {
        return $this->interval->d;
    }

    public function toArray()
    {
        $interval['years'] = $this->getYears();
        $interval['months'] = $this->getMonths();
        $interval['days'] = $this->getDays();
        $interval['hours'] = $this->getHours();
        $interval['minutes'] = $this->getMinutes();
        return $interval;
    }

    public static function create($interval)
    {
        $interval = new Self($interval);
        return $interval->toArray();
    }

    public static function isDurationInverval($text)
    {
        if (substr($text,0,1) == 'P') {
            return true;
        }
        return false;
    }

    
}
