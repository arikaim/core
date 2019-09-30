<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Collection;

use Arikaim\Core\FileSystem\File;
use Arikaim\Core\Collection\Properties;

/**
 * Properties factory class
 */
class PropertiesFactory
{ 
    /**
     * Create properties from array
     *
     * @param array $data
     * @return Properties
     */
    public static function createFromArray(array $data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $property = (is_array($value) == true) ? Property::create($value) : new Property($key,$value);           
            $result[$key] = $property;
        }    
        return new Properties($result,false);   
    }

    /**
     * Create from file
     *
     * @param string $file_name
     * @param boolean $resolve_properties
     * @return Properties
     */
    public static function createFromFile($file_name, $resolve_properties = true)
    {
        $data = File::readJSONFile($file_name);
        $data = (is_array($data) == true) ? $data : [];

        return ($resolve_properties == true) ? new Properties($data) : Self::createFromArray($data);       
    }
}
