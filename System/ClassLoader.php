<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\System;

class ClassLoader 
{

    private $base_path;
    private $core_path;
 
    public function __construct($base_path, $core_path = null) 
    {   
        if ($core_path == null) {
            $core_path = 'Arikaim' . DIRECTORY_SEPARATOR . 'Core';
        }
        $this->core_path = $core_path;
        $this->base_path = $base_path;
    }
   
    public function register() 
    {
        spl_autoload_register(array($this, 'LoadClassFile'));
    }

    function LoadClassFile($class) 
    {
        $file = $this->getClassFileName($class);
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }

    public function getClassFileName($class) 
    {   
        //echo __DIR__;
    //    echo ":" . $_SERVER['DOCUMENT_ROOT'];
        //$len = strlen($this->prefix);
      //  if (strncmp($this->prefix, $class, $len) !== 0) {
      //      return false;
      //  }
        $path = $_SERVER['DOCUMENT_ROOT'] . $this->base_path;
        $class_name = class_basename($class);
        $namespace = $this->getNamespace($class);
        $namespace = $this->namespaceToPath($namespace);   
      //  echo "n:$namespace p: $path";
        $file = $path . DIRECTORY_SEPARATOR .  $namespace . DIRECTORY_SEPARATOR . $class_name . ".php";

       // echo "ns:$namespace <br> cl:$class f:$file <br>";
        return $file;
    }

    public function getNamespace($full_class_name) 
    {    
        $rpos = strrpos($full_class_name,"\\");       
        $namespace  = substr($full_class_name,0,$rpos);
        return $namespace;   
    } 
        
    public function namespaceToPath($namespace, $full_path = false) 
    {    
        $namespace = str_replace("\\",DIRECTORY_SEPARATOR,$namespace);
        $namespace = str_replace($this->core_path,strtolower($this->core_path),$namespace);
       // $namespace = strtolower($namespace);
        if ($full_path == true) {
            $path = $_SERVER['DOCUMENT_ROOT'] . $this->base_path;
            $namespace = $path . DIRECTORY_SEPARATOR .  $namespace;
        }
        return $namespace;   
    } 
}
