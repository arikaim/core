<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\Validator\Rule;

use Arikaim\Core\Validator\Rule;
use Arikaim\Core\Utils\File;
use Arikaim\Core\System\Path;

/**
 *  Extension path rule. Check if extension path exists
 */
class ExtensionPath extends Rule
{  
    /**
     * Constructor
     *
     */
    public function __construct() 
    {
        parent::__construct([]);

        $this->setError("EXTENSION_NOT_EXISTS");
    }

    /**
     * Validate value
     *
     * @param mixed $value
     * @return boolean
     */
    public function validate($value) 
    {           
        return (File::exists(Path::EXTENSIONS_PATH . $value) == false) ? false : true;                    
    } 

    /**
     * Return filter type
     *
     * @return int
     */
    public function getType()
    {       
        return FILTER_CALLBACK;
    }
}
