<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\System;

/**
 * Class loader
 */
class ClassLoader 
{
    private $root_path;
    private $base_path;
    private $core_path;
    private $extensions_path;

    public function __construct($base_path, $root_path = null, $core_path = null, $extensions_path = null) 
    {   
        if ($core_path == null) {
            $core_path = 'Arikaim' . DIRECTORY_SEPARATOR . 'Core';
        }
        if ($extensions_path == null) {
            $extensions_path = 'Arikaim' . DIRECTORY_SEPARATOR . 'Extensions';
        }
        $this->root_path = $root_path;
        $this->core_path = $core_path;
        $this->extensions_path = $extensions_path;
        $this->base_path = $base_path;
    }
   
    public function register() 
    {
        spl_autoload_register(array($this, 'LoadClassFile'));
    }

    public function LoadClassFile($class) 
    {
        $file = $this->getClassFileName($class);
        if (file_exists($file) == true) {
            require $file;
            return true;
        }
        return false;
    }

    public function getDocumentRoot()
    {
        if ($this->root_path != null) {
            return $this->root_path;
        }
        if (php_sapi_name() == "cli") {
            return __DIR__;
        }
        return $_SERVER['DOCUMENT_ROOT'];
    }

    public function getClassFileName($class) 
    {   
        $path = $this->getDocumentRoot() . $this->base_path;
        $class_name = class_basename($class);
        $namespace = $this->getNamespace($class);
        $namespace = $this->namespaceToPath($namespace);   
        $file = $path . DIRECTORY_SEPARATOR .  $namespace . DIRECTORY_SEPARATOR . $class_name . ".php";
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
        
        if ($this->isExtensionsNamespace($namespace) == true) {
            $namespace = strtolower($namespace);
        } else {
            $namespace = str_replace($this->core_path,strtolower($this->core_path),$namespace);
        }

        if ($full_path == true) {
            $path = $this->getDocumentRoot() . $this->base_path;
            $namespace = $path . DIRECTORY_SEPARATOR .  $namespace;
        }
        return $namespace;   
    } 

    private function isExtensionsNamespace($namespace)
    {
        $parts = explode(DIRECTORY_SEPARATOR,$namespace);       
        if (isset($parts[1]) == true) {
            if (($parts[0] == "Arikaim") && (($parts[1] == "Extensions") || ($parts[1] == "Modules"))) {
                return true;
            }
        }        
        return false;
    }

    public function loadClassAlias($class_name,$alias)
    {
        if (class_exists($class_name) == true) {        
           return class_alias($class_name,$alias);
        }
        return false;
    }

    public function loadAlliases(array $items)
    {
        $errors = 0;
        foreach ($items as $alias => $class_name) {   
            $errors += ($this->loadClassAlias($class_name,$alias) == true) ? 0 : 1;
        }
        return ($errors > 0) ? false : true;
    }
}
