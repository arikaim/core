<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System;

use Arikaim\Core\Collection\Arrays;

/**
 * Session wrapper
 */
class Session 
{      
    /**
     * Default session lifetime value
     *
     * @var integer
     */
    private $defaultLifetime = 36000;

    /**
     * Constructor
     *
     * @param integer $lifetime
     */
    public function __construct($lifetime = null) 
    {
        $this->start($lifetime);    
    }

    /**
     * Start session
     *
     * @param integer|null $lifetime
     * @return void
     */
    public function start($lifetime = null) 
    {
        $lifetime = ($lifetime == null) ? $this->defaultLifetime : $lifetime;          
        $this->setLifetime($lifetime);

        if ($this->isStarted() == false) {
            session_start();
            $startTime = $this->getStartTime();
            $startTime = (empty($startTime) == true) ? time() : $startTime;
            $this->set('time_start',$startTime);  
            $this->set('lifetime',$lifetime);          
        }

        if ($this->isActive() == false) {
            session_cache_limiter(false);  
        }      
    }

    /**
     * Return true if session is started
     *
     * @return boolean
     */
    public function isStarted()
    {
        return !(session_status() == PHP_SESSION_NONE);
    }

    /**
     * Return true if session is active
     *
     * @return boolean
     */
    public function isActive() 
    {
        return (session_status() == PHP_SESSION_ACTIVE);
    }

    /**
     * Urecreate session
     *
     * @param integer $lifetime
     * @return bool
     */
    public function recrete($lifetime = null) 
    {
        $session = $this->toArray();
        $this->start($lifetime);

        foreach ($session as $key => $value) {
            $this->set($key,$value);
        }
        $this->set('time_start',time());   

        return true;
    }

    /**
     * Get session start time
     *
     * @return integer
     */
    public function getStartTime()
    {
        return $this->get('time_start');
    }

    /**
     * Get session end time.
     *
     * @return integer
     */
    public function getEndTime()
    {   
        return $this->getStartTime() + $this->getLifetime();
    }

    /**
     * Set session lifetime
     *
     * @param integer $time
     * @return void
     */
    public function setLifetime($time)
    {
        ini_set("session.cookie_lifetime",$time);
        ini_set("session.gc_maxlifetime",$time);
        session_set_cookie_params($time);
    }

    /**
     * Return session lifetime
     *
     * @return integer
     */
    public function getLifetime()
    {
        $info = session_get_cookie_params();
        return $info['lifetime'];
    }

    /**
     * Get session Id
     *
     * @return string
     */
    public function getId() 
    {
        $id = session_id();

        return (empty($id) == true) ? $this->getCookie('PHPSESSID') : $id;      
    }
    
    /**
     * Get session params
     *
     * @return array
     */
    public function getParams() 
    {
        return [
            'time_start' => $this->getStartTime(),
            'time_end'  => $this->getEndTime(),
            'lifetime'  => $this->getLifetime()
        ];
    }

    /**
     * Set value
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set($name, $value) 
    {
        $_SESSION[$name] = $value;
    }
    
    /**
     * Return session value or default value if session variable missing
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return (isset($_SESSION[$name]) == true) ? $_SESSION[$name] : $default;
    }
    
    /**
     * Return sesion var by path
     *
     * @param string $path
     * @return mixed
     */
    public function getValue($path)
    {
        return Arrays::getValue($_SESSION,$path);        
    }
    
    /**
     * Remove session value
     *
     * @param string $name
     * @return void
     */
    public function remove($name) 
    {
        unset($_SESSION[$name]);
    }
    
    /**
     * Destroy session
     * 
     * @param boolean $destoryCookie
     * @return void
     */
    public function destroy($destoryCookie = true)
    {
        if ($destoryCookie == true) {
            setcookie(session_id(),"",time() - 3600);
        }       
        session_destroy();
    }

    /**
     * Clear all session varibales and start new sesion
     * 
     * @param integer|null $lifetime
     * @return void
     */
    public function restart($lifetime = null)
    {
        session_unset();      
        $this->destroy();
      
        $this->start($lifetime);
    }

    /**
     * Get session status
     *
     * @return integer
     */
    public function getStatus()
    {
        return session_status();
    }

    /**
     * Get session array 
     *
     * @return array
     */
    public function toArray()
    {
        return (is_array($_SESSION) == true) ? $_SESSION : [];          
    }

    /**
     * Return true if session is stored in cookies
     *
     * @return boolean
     */
    public function isUseCookies() {
        return ini_get("session.use_cookies");
    }
}
