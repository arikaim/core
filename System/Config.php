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

use Arikaim\Core\Arikaim;
use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Collection\Collection;
use Arikaim\Core\System\Path;
use Arikaim\Core\Utils\Utils;

/**
 * Config file loader and writer
 */
class Config extends Collection
{
    /**
     * Config file name
     *
     * @var string
     */
    private $file_name;
    
    /**
     * Config array comments
     *
     * @var array
     */
    private $comments = [];

    /**
     * Cache
     *
     * @var array|null
     */
    private $cache;

    /**
     * Constructor
     *
     * @param string $file_name
     * @param array|null $cache
     */
    public function __construct($file_name = null, $cache = null) 
    {       
        $this->cache = $cache;
        $this->file_name = (empty($file_name) == true) ? 'config.php' : $file_name;
        $data = $this->load($this->file_name);   
       
        parent::__construct($data);   

        $this->setComment('database settings','db');
        $this->setComment('application settings','settings');
    }
    
    public static function read($file_name) 
    {
        $instance = new Self();
        return $instance->load($file_name);
    }

    /**
     * Load config file
     *
     * @param string $file_name
     * @return array
     */
    public function load($file_name) 
    {
        $cache_id = strtolower($file_name);
        if (is_null($this->cache) == false) {
            $result = $this->cache->fetch($cache_id);
            if (is_array($result) == true) {
                return $result;
            }
        }
      
        $file_name = Path::CONFIG_PATH . $file_name;
       
        $result = (File::exists($file_name) == true) ? include($file_name) : [];    
        if (is_null($this->cache) == false && (empty($result) == false)) {
            $this->cache->save($cache_id,$result);
        } 
        return $result;            
    }   

    /**
     * Set array key comment
     *
     * @param string $comment
     * @param string $key
     * @return void
     */
    protected function setComment($comment, $key)
    {
        $this->comments[$key] = $comment;
    }

    /**
     * Get array imtem comment as text
     *
     * @param string $key
     * @return string
     */
    protected function getCommentsText($key)
    {
        return (isset($this->comments[$key]) == true) ? "\t// " . $this->comments[$key] . "\n" : '';
    }

    /**
     * Return config file content
     *
     * @return string
     */
    private function getFileContent($data) 
    {   
        $code = $this->getFileContentHeader();
        $code .= $this->exportConfig($data);

        return $code;
    }

    /**
     * Export array as text
     *
     * @param array $data
     * @param string $array_key
     * @return string
     */
    protected function exportArray(array $data, $array_key)
    {     
        $items = "";  
        $max_tabs = $this->determineMaxTabs($data);
    
        foreach ($data as $key => $value) {
            $items .= (empty($items) == false) ? ",\n" : "";
            $value = Utils::getValueAsText($value);
            $tabs = $max_tabs - $this->determineTabs($key);
            $items .="\t\t'$key'" . $this->getTabs($tabs) . "=> $value";
        }
        $comment = $this->getCommentsText($array_key);
        return "$comment\t'" . $array_key . "' => [\n" . $items . "\n\t]";
    }

    /**
     * Export item as text
     *
     * @param string $key
     * @param mixed $value
     * @param integer $max_tabs
     * @return string
     */
    protected function exportItem($key, $value, $max_tabs)
    {
        $tabs = $max_tabs - $this->determineTabs($key);
        $value = Utils::getValueAsText($value);
        return "\t'$key'" . $this->getTabs($tabs) . "=> $value";
    }

    /**
     * Export config as text
     *
     * @return string
     */
    protected function exportConfig($data)
    {
        $items = '';
        $max_tabs = $this->determineMaxTabs($data);

        foreach ($data as $key => $item) {
            if (is_array($item) == true) {
                $items .= (empty($items) == false) ? ",\n" : "";
                $items .= $this->exportArray($item,$key);
            } else {
                $items .= (empty($items) == false) ? ",\n" : "";
                $items .= $this->exportItem($key,$item,$max_tabs);
            }
        }
        return "return [\n $items \n];\n";      
    }

    /**
     * Get config file header
     *
     * @return string
     */
    private function getFileContentHeader() 
    {
        $code = "<?php \n/**\n";
        $code .= "* Arikaim\n";
        $code .= "* @link        http://www.arikaim.com\n";
        $code .= "* @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>\n";
        $code .= "* @license     http://www.arikaim.com/license.html\n";
        $code .= "*/\n\n";

        return $code;
    }

    /**
     * Save config file
     *
     * @param string|null $file_name
     * @return bool
     */
    public function save($file_name = null, $data = null)
    {
        $file_name = (empty($file_name) == true) ? $this->file_name : $file_name;
        $data = (empty($data) == true) ? $this->data : $data;

        if (is_null($this->cache) == false) {
            $cache_id = strtolower($file_name);
            $this->cache->delete($cache_id);
        }
       
        $full_file_name = Path::CONFIG_PATH . $file_name;
        if (File::isWritable($full_file_name) == false) {
            File::setWritable($full_file_name);
        }
        $content = $this->getFileContent($data);  
     
        return File::write($full_file_name,$content);       
    }

    /**
     * Load json config file
     *
     * @param string $file_name
     * @return array
     */
    public static function loadJsonConfigFile($file_name = null)
    {
        $data = File::readJSONFile(Path::CONFIG_PATH . $file_name);
        $data = (is_array($data) == true) ? $data : [];

        $items = new Collection($data);
        $items->addField("status",1);
        $items->addField("order",0);
        $items->addField("default",0);

        return $items->toArray();
    }

    /**
     * Get max tabs count
     *
     * @param array $data
     * @param integer $tab_size
     * @return integer
     */
    private function determineMaxTabs(array $data, $tab_size = 4)
    {
        $keys = [];
        foreach ($data as $key => $value) {
            array_push($keys,strlen($key));
        }
        return ceil(max($keys) / $tab_size);
    }

    /**
     * Get tabs count for array key
     *
     * @param string $key
     * @param integer $tab_size
     * @return integer
     */
    private function determineTabs($key, $tab_size = 4)
    {
        return round(strlen($key) / $tab_size);
    }

    /**
     * Get tabs text
     *
     * @param integer $count
     * @return string
     */
    private function getTabs($count)
    {
        $result = "";
        for ($index = 0; $index <= $count; $index++) {
            $result .="\t";
        }
        return $result;
    }
}
