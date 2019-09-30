<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\System;

/**
 * Class loader
 */
class ClassLoader 
{
    /**
     * Root classes path
     *
     * @var string
     */
    private $root_path;

    /**
     * Base path
     *
     * @var string
     */
    private $base_path;

    /**
     * Arikaim core path
     *
     * @var string
     */
    private $core_path;

    /**
     * Arikaim extensions path.
     *
     * @var string
     */
    private $extensions_path;

    /**
     * Namepaces
     *
     * @var array
     */
    private $namespaces = ['Extensions','Modules'];

    /**
     * Constructor
     *
     * @param string $base_path
     * @param string $root_path
     * @param string $core_path
     * @param string $extensions_path
     */
    public function __construct($base_path, $root_path = null, $core_path = null, $extensions_path = null) 
    {   
        $this->root_path = $root_path;
        $this->core_path = $core_path;
        $this->extensions_path = $extensions_path;
        $this->base_path = $base_path;
    }
    
    /**
     * Register loader
     * 
     * @return void
     */
    public function register() 
    {
        spl_autoload_register(array($this, 'LoadClassFile'));
    }

    /**
     * Load class file
     *
     * @param string $class
     * @return bool
     */
    public function LoadClassFile($class) 
    {
        $file = $this->getClassFileName($class);
        if (file_exists($file) == true) {
            require $file;
            return true;
        }
        return false;
    }

    /**
     * Get root path
     *
     * @return string
     */
    public function getDocumentRoot()
    {
        if ($this->root_path != null) {
            return $this->root_path;
        }
        return (php_sapi_name() == "cli") ? __DIR__ : $_SERVER['DOCUMENT_ROOT'];         
    }

    /**
     * Get class file name
     *
     * @param string $class
     * @return string
     */
    public function getClassFileName($class) 
    {   
        $path = $this->getDocumentRoot() . $this->base_path;
        $class_name = class_basename($class);
        $namespace = $this->getNamespace($class);
        $namespace = $this->namespaceToPath($namespace);   
        return $path . DIRECTORY_SEPARATOR .  $namespace . DIRECTORY_SEPARATOR . $class_name . ".php";       
    }

    /**
     * Get namspace
     *
     * @param string $full_class_name
     * @return string
     */
    public function getNamespace($full_class_name) 
    {    
        $rpos = strrpos($full_class_name,"\\");       
        return substr($full_class_name,0,$rpos);       
    } 
    
    /**
     * Convert namespace to path
     *
     * @param string $namespace
     * @param boolean $full_path
     * @return string
     */
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

    /**
     * Return true if namespace is extension namespace
     *
     * @param [type] $namespace
     * @return boolean
     */
    private function isExtensionsNamespace($namespace)
    {
        $parts = explode(DIRECTORY_SEPARATOR,$namespace);       
        if (isset($parts[1]) == true) {
            if ($parts[0] == "Arikaim" && in_array($parts[1],$this->namespaces) == true) {
                return true;
            }
        }        
        return false;
    }

    /**
     *  Load class alias
     *
     * @param string $class_name
     * @param string $alias
     * @return bool
     */
    public function loadClassAlias($class_name,$alias)
    {
        return (class_exists($class_name) == true) ? class_alias($class_name,$alias) : false;                
    }

    /**
     * Load class aliaeses
     *
     * @param array $items
     * @return bool
     */
    public function loadAlliases(array $items)
    {                
        foreach ($items as $class_name => $alias) {      
            if ($this->loadClassAlias($class_name,$alias) == false) { 
                throw new \Exception("Error load class alias for class ($class_name) alias ($alias)", 1);      
                return false;
            }
        }
        return true;
    }
}
