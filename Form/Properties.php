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

use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Form\Form;

class Properties extends Form
{
    private $filters;

    public function __construct($json_file_name = null, $root_element = null, $vars = null) 
    {
        parent::__construct();
        if ($json_file_name != null) {
            $this->load($json_file_name, $root_element,$vars);
        }
    }

    public function load($json_file_name, $root_element = null, $vars = null) 
    {
        $data = File::loadJSONFile($json_file_name,$vars);
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
                $current_value = Utils::arrayGetValue($item,$path);
                if (empty($current_value) == true) {
                    $this->data[$key] = Utils::arraySetValue($item,$path,$value);
                }
            }
        }
        return true;
    }

    public function clear()
    {
        $this->data = [];
    }

    public function getByPath($path)
    {
        return Utils::arrayGetValue($this->data,$path);
    }
}
