<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Validator\Rule;

use Arikaim\Core\Validator\Rule;
use Arikaim\Core\FileSystem\File;
use Arikaim\Core\System\Path;

/**
 *  Extension path rule. Check if extension path exists
 */
class ExtensionPath extends Rule
{  
    protected $extension_name;

    /**
     * Constructor
     *
     * @param string $extension_name
     * @param string $error
     */
    public function __construct($extension_name, $error = "EXTENSION_NOT_EXISTS") 
    {
        parent::__construct(null,null,$error);
        $this->extension_name = $extension_name; 
    }

    /**
     * Validate value
     *
     * @param mixed $value
     * @return boolean
     */
    public function customFilter($value) 
    {           
        return (File::exists(Path::EXTENSIONS_PATH . $value) == false) ? false : true;                    
    } 

    /**
     * Return filter type
     *
     * @return int
     */
    public function getFilter()
    {       
        return FILTER_CALLBACK;
    }

    /**
     * Return filter options
     *
     * @return array
     */
    public function getFilterOptions()
    {
        return $this->getCustomFilterOptions();
    }
}
