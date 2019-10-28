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
    private $timeZone;

    /**
     * DateTime object
     *
     * @var DateTime
     */
    private $dateTime;

    /**
     * Time zone name
     *
     * @var string
     */
    private $timeZoneName;

    /**
     * Date format
     *
     * @var string
     */
    private $dateFormat;

    /**
     * Constructor
     * 
     * @param string|null $date
     * @param string|null $format
     */
    public function __construct($date = null, $format = null) 
    {
        if (is_object(Arikaim::options()) == true) {
            $this->timeZoneName = Arikaim::options()->get('time.zone');
        }
        if ($this->timeZoneName == null) {
            $this->timeZoneName = Self::getDefaultTimeZoneName();
        }
        $this->timeZone = new \DateTimeZone($this->timeZoneName);
        $date = (empty($date) == true) ? 'now' : $date;

        $this->dateFormat = Self::getDateFormat($format);
        $this->dateTime = new \DateTime($date,$this->timeZone);
        $this->setDateFormat($this->dateFormat);
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
     * @param string|null $dateFormat
     * @return string
     */
    public static function getDateFormat($dateFormat = null) 
    {      
        if ($dateFormat == null) {
            $dateFormat = (is_object(Arikaim::options()) == true) ? Arikaim::options()->get('date.format') : DateTime::DEFAULT_DATE_FORMAT;               
        }

        return $dateFormat;
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
     * @param string $timeFormat
     * @return void
     */
    public static function getTimeFormat($timeFormat = null) 
    {       
        if ($timeFormat == null) {
            $timeFormat = (is_object(Arikaim::options()) == true) ? Arikaim::options()->get('time.format') : DateTime::DEFAULT_TIME_FORMAT;             
        }

        return $timeFormat;
    }

    /**
     * Get interval details
     *
     * @param string $intervalText
     * @return array
     */
    public function getInterval($intervalText)
    {
        $interval = new TimeInterval($intervalText);

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
        return $this->timeZone->getLocation();
    }

    /**
     * Get time zone offset
     *
     * @return string
     */
    public function getTimeZoneOffset() 
    {
        return $this->timeZone->getOffset($this->dateTime);
    }

    /**
     * Get time zone
     *
     * @return string
     */
    public function getTimeZoneName() 
    {
        return $this->timeZoneName;
    }

    /**
     * Set date format.
     *
     * @param string $dateFormat
     * @return DateTime
     */
    public function setDateFormat($dateFormat) 
    {
        $this->dateTime->format($dateFormat);

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
        $this->dateTime->modify($date_text);

        return $this;
    }

    /**
     * Add interval
     *
     * @param string $dateInterval
     * @return DateTime
     */
    public function addInterval($dateInterval)
    {
        $interval = new \DateInterval($dateInterval); 
        $this->dateTime->add($interval); 

        return $this;
    }
    
    /**
     * Sub interval
     *
     * @param string $dateInterval
     * @return DateTime
     */
    public function subInterval($dateInterval)
    {
        $interval = new \DateInterval($dateInterval); 
        $this->dateTime->sub($interval); 
        
        return $this;
    }

    /**
     * Set timestamp
     *
     * @param integer $unixTimestamp
     * @return DateTime
     */
    public function setTimestamp($unixTimestamp) 
    {
        $this->dateTime->setTimestamp($unixTimestamp);

        return $this;
    }

    /**
     * Get timestamp
     *
     * @return integer
     */
    public function getTimestamp()
    {
        return $this->dateTime->getTimestamp();
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
        return $this->dateTime;
    }

    /**
     * Convert current date time to string.
     *
     * @param string $format
     * @return string
     */
    public function toString($format = null) 
    {
        $format = (empty($format) == true) ? $this->dateFormat : $format;         

        return $this->dateTime->format($format);
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
