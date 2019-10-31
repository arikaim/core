<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
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
     * @param boolean $resolveProperties
     * @return Properties
     */
    public static function createFromFile($file_name, $resolveProperties = true)
    {
        $data = File::readJsonFile($file_name);
        $data = (is_array($data) == true) ? $data : [];

        return ($resolveProperties == true) ? new Properties($data) : Self::createFromArray($data);       
    }
}
