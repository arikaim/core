<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Form;

use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\Form\Form;

class Properties extends Form
{
    public function __construct($json_file_name = null, $root_element = null, $vars = null) 
    {
        parent::__construct();
        if (empty($json_file_name) == false) {
            $this->load($json_file_name, $root_element,$vars);
        }
    }

    public function load($json_file_name, $root_element = null, $vars = null) 
    {
        $data = File::readJSONFile($json_file_name,$vars);
        if ($data != false) {
            if ($root_element != null) {
                $this->data = $data[$root_element];
            } else {
                $this->data = $data;
            }
            return true;
        }
        return false;
    }

    public function addField($path, $value)
    {
        foreach ($this->data as $key => $item) {
            if (is_array($item) == true) {
                $current_value = Arrays::getValue($item,$path);
                if ($current_value === null) {
                    $this->data[$key] = Arrays::setValue($item,$path,$value);
                }
            }
        }
        return true;
    }

    public function clear()
    {
        $this->data = [];
    }

    public function getByPath($path, $default_value = null)
    {
        $value = Arrays::getValue($this->data,$path);
        if (($value == null) && ($default_value != null)) {
            $value = $default_value;
        }
        return $value;
    }
}
