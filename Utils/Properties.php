<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Utils;

use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Utils\Collection;

class Properties extends Collection
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
            $this->data = (isset($data[$root_element]) == true) ? $data[$root_element] : $data;
            return true;
        }
        return false;
    }
}
