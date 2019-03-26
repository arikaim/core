<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System;

use Arikaim\Core\Utils\Arrays;

class Session 
{      
    private $default_lifetime = 36000;

    public function __construct($lifetime = null) 
    {
        $this->start($lifetime);    
    }

    public function start($lifetime = null) 
    {
        if ($lifetime == null) {
           $lifetime = $this->default_lifetime;
        }
        $this->setLifetime($lifetime);
        if ($this->isStarted() == false) {
            session_start();   
        }
         
        session_cache_limiter(false);  
        $this->set('time_start',time());      
        $this->set('lifetime',$lifetime);      
    }

    public function isStarted()
    {
        return (empty(session_id()) == false) ? true : false;
    }

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

    public function getStartTime()
    {
        return $this->get('time_start');
    }

    public function getEndTime()
    {   
        return $this->getStartTime() + $this->getLifetime();
    }

    public function setLifetime($time)
    {
        ini_set("session.cookie_lifetime",$time);
        ini_set("session.gc_maxlifetime",$time);
        session_set_cookie_params($time);
    }

    public function getLifetime()
    {
        $info = session_get_cookie_params();
        return $info['lifetime'];
    }

    public function getId() 
    {
        $id = session_id();
        if (empty($id) == true) {
            $id = $this->getCookie('PHPSESSID');
        }
        return $id;
    }
    
    public function getParams() 
    {
        $session_info['time_start'] = $this->getStartTime();
        $session_info['time_end']  = $this->getEndTime();
        $session_info['lifetime']  = $this->getLifetime();
        return $session_info;
    }

    public function set($name, $value) 
    {
        $_SESSION[$name] = $value;
    }
    
    public function setMulti($base, $key, $value) 
    {
        $_SESSION[$base][$key] = $value;
    }
    
    public function get($name,$default_value = null)
    {
        return (isset($_SESSION[$name]) == true) ? $_SESSION[$name] : $default_value;
    }
    
    public function getValue($path)
    {
        return Arrays::getValue($_SESSION,$path);        
    }
    
    public function remove($name) 
    {
        unset($_SESSION[$name]);
    }
    
    public function destroy()
    {
        session_destroy();
    }

    public function getStatus()
    {
        return session_status();
    }

    public function toArray()
    {
        return (is_array($_SESSION) == true) ? $_SESSION : [];          
    }

    public function isUseCookies() {
        return ini_get("session.use_cookies");
    }
}
