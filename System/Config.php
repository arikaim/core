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

use Arikaim\Core\Arikaim;
use Arikaim\Core\Form\Properties;
use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Utils\Collection;

class Config extends Collection implements \ArrayAccess 
{
    public function __construct($file_name = null) 
    {
        parent::__construct();    
        $file_name = ($file_name == null) ? "config.php" : $file_name;  
        $this->loadConfig($file_name);
    }

    public function loadConfig($file_name = null) 
    {
        $config = $this->includeConfigFile($file_name); 
        if (is_array($config) == false) {     
            $this->showError();
        }
        $this->data = $config;
        return true;
    }   

    private function showError()
    {
        echo "**** Not valid config file. ****";
        Arikaim::end();
    }

    private function getFileContent() 
    {    
        $code = $this->getFileContentHeader();

        $code .= $this->getConfigArrayCode('db','database settings');   
        $code .= $this->getConfigArrayCode('settings','application settings');       
       
        $code .= $this->addLine('settings','settings');
        $code .= $this->addLine('db','db');
        $code .='return $config;'."\n";
        return $code;
    }

    private function addLine($array_name,$key_name = 'config', $sub_key = null)
    {
        $key = $this->getKey($key_name);
        $sub_key = $this->getKey($sub_key);
        return '$config' . $key . $sub_key . ' = $' . $array_name . ";\n";
    }

    private function getKey($key_name)
    {
        if ($key_name != "") {
            return '[\'' . $key_name . '\']';
        }
        return "";
    }

    private function getConfigArrayCode($config_key,$comment = "")
    {
        $code = "// " . $comment . "\n"; 
        foreach ($this->data[$config_key] as $key => $value) {
            $value = $this->getValueAsText($value);
            $code .='$' . $config_key . '[\'' . $key .'\'] = ' . $value . ";\n";
        }
        return $code . "\n";
    }

    private function getValueAsText($value)
    {
        if (gettype($value) == "boolean") {
            if ($value == false) return "false"; 
            if ($value == true)  return "true"; 
        }       
        return "\"$value\"";
    }

    private function getFileContentHeader() 
    {
        $code = "<?php \n/**\n";
        $code .= "* Arikaim\n";
        $code .= "* @link        http://www.arikaim.com\n";
        $code .= "* @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>\n";
        $code .= "* @license     http://www.arikaim.com/license.html\n";
        $code .= "*/\n\n";
        return $code;
    }

    public function saveConfigFile($file_name = null)
    {
        $file_name = $this->getFileName($file_name);
        if (File::isWritable($file_name) == false) {
            File::setWritable($file_name);
        }
        $content = $this->getFileContent();     
        $result = File::write($file_name,$content);
        return $result;
    }

    public function loadJsonConfigFile($file_name = null)
    {
        $file_name = $this->getFileName($file_name);
        $items = new Properties($file_name,"items");
        $items->addField("status",1);
        $items->addField("order",0);
        $items->addField("default",0);
        return $items->toArray();
    }

    public function includeConfigFile($file_name = null) 
    {
        $file_name = $this->getFileName($file_name);
        if (File::exists($file_name) == true) {           
            $result = include($file_name);
            return $result;
        }
        return false;
    }

    public static function getConfigPath() 
    {
        return Arikaim::getRootPath() . Arikaim::getBasePath() . DIRECTORY_SEPARATOR . 'arikaim' . DIRECTORY_SEPARATOR . 'config';
    }

    private function getFileName($file_name) {
        return Self::getConfigPath() . DIRECTORY_SEPARATOR . $file_name;
    }

    /**
     * Implement ArrayAccess interface function
     *
     * @param string $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Implement ArrayAccess interface function
     *
     * @param string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->data[$key];
    }

    /**
     * Implement ArrayAccess interface function
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Implement ArrayAccess interface function
     *
     * @param string $key
     * @return void
     */
    public function offsetUnset($key)
    {
       unset($this->data[$key]);
    }
}
