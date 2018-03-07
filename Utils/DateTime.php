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
use Arikaim\Core\Utils\TimeInterval;

class DateTime 
{   
    const DEFAULT_DATE_FORMAT = 'Y-m-d';
    const DEFAULT_TIME_FORMAT = 'H:i';

    private $time_zone;
    private $date_time;
    private $time_zone_name;
    private $date_format;

    public function __construct() 
    {
        if (is_object(Arikaim::options()) == true) {
            $this->time_zone_name = Arikaim::options()->get('time.zone');
        }
        if ($this->time_zone_name == null) {
            $this->time_zone_name = Self::getDefaultTimeZoneName();
        }
        $this->date_format = Self::getDateFormat();
        $this->time_zone = new \DateTimeZone($this->time_zone_name);

        $this->date_time = new \DateTime("now",$this->time_zone);
        $this->setDateFormat($this->date_format);
    }

    public static function getDateFormat($date_format = null) 
    {      
        if ($date_format == null) {
            if (is_object(Arikaim::options()) == true) {
                $date_format = Arikaim::options()->get('date.format');
            } else {
                $date_format = DateTime::DEFAULT_DATE_FORMAT;
            }
        }
        return $date_format;
    }

    public static function getTimeFormat($time_format = null) 
    {       
        if ($time_format == null) {
            if (is_object(Arikaim::options()) == true) {
                $time_format = Arikaim::options()->get('time.format');
            } else {
                $time_format = DateTime::DEFAULT_TIME_FORMAT;
            }
        }
        return $time_format;
    }

    public function getInterval($interval_text)
    {
        $interval = new TimeInterval($interval_text);
        return $interval->toArray();
    }

    public function getTimeZonesList()
    {
        $items = timezone_identifiers_list();
        return $items;
    }

    public function getLocation() 
    {
        return $this->time_zone->getLocation();
    }

    public function getTimeZoneOffset() 
    {
        return $this->time_zone->getOffset($this->date_time);
    }

    public function getTimeZoneName() 
    {
        return $this->time_zone_name;
    }

    public function setDateFormat($date_format) 
    {
        $this->date_time->format($date_format);
    }

    public function modify($date_text) 
    {
        $this->date_time->modify($date_text);
    }

    public function addInterval($date_interval)
    {
        $interval = new \DateInterval($date_interval); 
        $this->date_time->add($interval); 
    }
    
    public function subInterval($date_interval)
    {
        $interval = new \DateInterval($date_interval); 
        $this->date_time->sub($interval); 
    }

    public function setTimestamp($unix_timestamp) 
    {
        $this->date_time->setTimestamp($unix_timestamp);
    }

    public function getTimestamp()
    {
        return $this->date_time->getTimestamp();
    }

    public function getYear()
    {
        return date('Y',$this->getTimestamp());
    }

    public function getMonth()
    {
        return date('n',$this->getTimestamp());
    }
    
    public function getDay()
    {
        return date('j',$this->getTimestamp());
    }

    public function getHour()
    {
        return date('G',$this->getTimestamp());
    }

    public function getMinutes()
    {
        $minutes = date('i',$this->getTimestamp());
        return intval($minutes);
    }

    public static function getDefaultTimeZoneName() {
        return date_default_timezone_get();
    }

    public static function getCurrentTime()
    {
        return time();
    }

    public function toString($format = null) 
    {
        if ($format == null) {
            $format = $this->date_format;
        }
        return $this->date_time->format($format);
    }

    public function __toString() 
    {
        return $this->date_time->format($this->date_format);
    }
}
