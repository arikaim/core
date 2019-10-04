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

use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\TimeInterval;

/**
 * DateTime
 */
class DateTime 
{   
    const DEFAULT_DATE_FORMAT = 'Y-m-d';
    const DEFAULT_TIME_FORMAT = 'H:i';

    /**
     * Time zone
     *
     * @var object
     */
    private $time_zone;

    /**
     * DateTime object
     *
     * @var DateTime
     */
    private $date_time;

    /**
     * Time zone name
     *
     * @var string
     */
    private $time_zone_name;

    /**
     * Date format
     *
     * @var string
     */
    private $date_format;

    /**
     * Constructor
     * 
     * @param string|null $date
     * @param string|null $format
     */
    public function __construct($date = null, $format = null) 
    {
        if (is_object(Arikaim::options()) == true) {
            $this->time_zone_name = Arikaim::options()->get('time.zone');
        }
        if ($this->time_zone_name == null) {
            $this->time_zone_name = Self::getDefaultTimeZoneName();
        }
        $this->time_zone = new \DateTimeZone($this->time_zone_name);
        $date = (empty($date) == true) ? 'now' : $date;

        $this->date_format = Self::getDateFormat($format);
        $this->date_time = new \DateTime($date,$this->time_zone);
        $this->setDateFormat($this->date_format);
    }

    /**
     * Create DateTime obj
     *
     * @param string|null $date
     * @param string|null $format
     * @return DateTime
     */
    public static function create($date = null, $format = null)
    {
        return new Self($date,$format);
    }

    /**
     * Comvert date time to timestamp
     *
     * @param string|null $date
     * @param string|null $format
     * @return integer
     */
    public static function toTimestamp($date = null, $format = null)
    {
        return Self::create($date,$format)->getTimestamp();    
    }

    /**
     * Get date format
     *
     * @param string|null $date_format
     * @return string
     */
    public static function getDateFormat($date_format = null) 
    {      
        if ($date_format == null) {
            $date_format = (is_object(Arikaim::options()) == true) ? Arikaim::options()->get('date.format') : DateTime::DEFAULT_DATE_FORMAT;               
        }
        return $date_format;
    }

    /**
     * Return formated timestsamp with current date and time format
     *
     * @param integer $timestamp
     * @param string|null $format
     * @return string
     */
    public static function dateTimeFormat($timestamp, $format = null)
    {
        if (is_numeric($timestamp) == false) {
            return $timestamp;
        }
        
        if ($format == null) {           
            $format = Self::getDateFormat() . " " . Self::getTimeFormat();
        }

        $date = new Self(null,$format);
        return $date->setTimestamp($timestamp)->toString();      
    }

    /**
     * Return formated time
     *
     * @param integer $timestamp
     * @param string $format
     * @return string
     */
    public static function timeFormat($timestamp, $format = null)
    {
        if (is_integer($timestamp) == false) {
            return $timestamp;
        }
        $format = (empty($format) == true) ? Self::getTimeFormat() : $format;    

        $date = new Self(null,$format);
        return $date->setTimestamp($timestamp)->toString();     
    }

    /**
     * Return formated date
     *
     * @param integer $timestamp
     * @param string $format
     * @return string
     */
    public static function dateFormat($timestamp, $format = null)
    {
        if (is_numeric($timestamp) == false) {
            return $timestamp;
        }
        $format = (empty($format) == true) ? Self::getDateFormat() : $format;   

        $date = new Self(null,$format);
        return $date->setTimestamp($timestamp)->toString();     
    }

    /**
     * Get time format
     *
     * @param string $time_format
     * @return void
     */
    public static function getTimeFormat($time_format = null) 
    {       
        if ($time_format == null) {
            $time_format = (is_object(Arikaim::options()) == true) ? Arikaim::options()->get('time.format') : DateTime::DEFAULT_TIME_FORMAT;             
        }
        return $time_format;
    }

    /**
     * Get interval details
     *
     * @param string $interval_text
     * @return array
     */
    public function getInterval($interval_text)
    {
        $interval = new TimeInterval($interval_text);
        return $interval->toArray();
    }

    /**
     * Get time zone list
     *
     * @return array
     */
    public function getTimeZonesList()
    {
        return timezone_identifiers_list();
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation() 
    {
        return $this->time_zone->getLocation();
    }

    /**
     * Get time zone offset
     *
     * @return string
     */
    public function getTimeZoneOffset() 
    {
        return $this->time_zone->getOffset($this->date_time);
    }

    /**
     * Get time zone
     *
     * @return string
     */
    public function getTimeZoneName() 
    {
        return $this->time_zone_name;
    }

    /**
     * Set date format.
     *
     * @param string $date_format
     * @return DateTime
     */
    public function setDateFormat($date_format) 
    {
        $this->date_time->format($date_format);

        return $this;
    }

    /**
     * Modify date time
     *
     * @param string $date_text
     * @return DateTime
     */
    public function modify($date_text) 
    {
        $this->date_time->modify($date_text);

        return $this;
    }

    /**
     * Add interval
     *
     * @param string $date_interval
     * @return DateTime
     */
    public function addInterval($date_interval)
    {
        $interval = new \DateInterval($date_interval); 
        $this->date_time->add($interval); 

        return $this;
    }
    
    /**
     * Sub interval
     *
     * @param string $date_interval
     * @return DateTime
     */
    public function subInterval($date_interval)
    {
        $interval = new \DateInterval($date_interval); 
        $this->date_time->sub($interval); 
        
        return $this;
    }

    /**
     * Set timestamp
     *
     * @param integer $unix_timestamp
     * @return DateTime
     */
    public function setTimestamp($unix_timestamp) 
    {
        $this->date_time->setTimestamp($unix_timestamp);

        return $this;
    }

    /**
     * Get timestamp
     *
     * @return integer
     */
    public function getTimestamp()
    {
        return $this->date_time->getTimestamp();
    }

    /**
     * Get curent year
     *
     * @return string
     */
    public static function getYear()
    {
        return date('Y',Self::toTimestamp());
    }

    /**
     * Get current month
     *
     * @return string
     */
    public static function getMonth()
    {
        return date('n',Self::getTimestamp());
    }
    
    /**
     * Return current day
     *
     * @return string
     */
    public static function getDay()
    {
        return date('j',Self::getTimestamp());
    }

    /**
     * Return current hour
     *
     * @return string
     */
    public static function getHour()
    {
        return date('G',Self::getTimestamp());
    }

    /**
     * Get current minutes
     *
     * @return integer
     */
    public function getMinutes()
    {
        return intval(date('i',Self::getTimestamp()));
    }

    /**
     * Return default time zone
     *
     * @return string
     */
    public static function getDefaultTimeZoneName() {
        return date_default_timezone_get();
    }

    /**
     * Return current time
     *
     * @return integer
     */
    public static function getCurrentTime()
    {
        $date = new Self();
        return $date->getTimestamp();
    }

    /**
     * Get DateTime
     *
     * @return DateTime
     */
    public function getDateTime()
    {
        return $this->date_time;
    }

    /**
     * Convert current date time to string.
     *
     * @param string $format
     * @return string
     */
    public function toString($format = null) 
    {
        $format = (empty($format) == true) ? $this->date_format : $format;         
        return $this->date_time->format($format);
    }

    /**
     * Convert object to string
     *
     * @return string
     */
    public function __toString() 
    {
        return $this->toString();
    }
}
