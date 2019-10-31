<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
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
    private $rootPath;

    /**
     * Base path
     *
     * @var string
     */
    private $basePath;

    /**
     * Arikaim core path
     *
     * @var string
     */
    private $corePath;

    /**
     * Arikaim extensions path.
     *
     * @var string
     */
    private $extensionsPath;

    /**
     * Namepaces
     *
     * @var array
     */
    private $namespaces = ['Extensions','Modules'];

    /**
     * Constructor
     *
     * @param string $basePath
     * @param string $rootPath
     * @param string $corePath
     * @param string $extensionsPath
     */
    public function __construct($basePath, $rootPath = null, $corePath = null, $extensionsPath = null) 
    {   
        $this->rootPath = $rootPath;
        $this->corePath = $corePath;
        $this->extensionsPath = $extensionsPath;
        $this->basePath = $basePath;
    }
    
    /**
     * Register loader
     * 
     * @return void
     */
    public function register() 
    {
        spl_autoload_register(array($this,'LoadClassFile'));
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
        if ($this->rootPath != null) {
            return $this->rootPath;
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
        $path = $this->getDocumentRoot() . $this->basePath;     
        $namespace = $this->getNamespace($class);
        $class = class_basename($class);
        $namespace = $this->namespaceToPath($namespace); 

        return $path . DIRECTORY_SEPARATOR .  $namespace . DIRECTORY_SEPARATOR . $class . ".php";       
    }

    /**
     * Get namspace
     *
     * @param string $class
     * @return string
     */
    public function getNamespace($class) 
    {           
        return substr($class,0,strrpos($class,"\\"));       
    } 
    
    /**
     * Convert namespace to path
     *
     * @param string $namespace
     * @param boolean $full
     * @return string
     */
    public function namespaceToPath($namespace, $full = false) 
    {  
        $namespace = str_replace("\\",DIRECTORY_SEPARATOR,$namespace);
        
        if ($this->isExtensionsNamespace($namespace) == true) {
            $namespace = strtolower($namespace);
        } else {
            $namespace = str_replace($this->corePath,strtolower($this->corePath),$namespace);
        }

        if ($full == true) {
            $path = $this->getDocumentRoot() . $this->basePath;
            $namespace = $path . DIRECTORY_SEPARATOR .  $namespace;
        }
       
        return $namespace;   
    } 

    /**
     * Return true if namespace is extension namespace
     *
     * @param string $namespace
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
     * @param string $class
     * @param string $alias
     * @return bool
     */
    public function loadClassAlias($class, $alias)
    {
        return (class_exists($class) == true) ? class_alias($class,$alias) : false;                
    }

    /**
     * Load class aliaeses
     *
     * @param array $items
     * @return bool
     */
    public function loadAlliases(array $items)
    {                
        foreach ($items as $class => $alias) {      
            if ($this->loadClassAlias($class,$alias) == false) { 
                throw new \Exception("Error load class alias for class ($class) alias ($alias)", 1);      
                return false;
            }
        }
        
        return true;
    }
}
