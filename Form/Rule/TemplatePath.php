<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Form\Rule;

use Arikaim\Core\Form\AbstractRule;
use Arikaim\Core\View\Template;
use Arikaim\Core\FileSystem\File;

/**
 *  Check if template file exist.
 */
class TemplatePath extends AbstractRule
{  
    protected $extension_name;

    /**
     * Constructor
     *
     * @param string $extension_name
     * @param string $error
     */
    public function __construct($extension_name, $error = "TEMPLATE_NOT_EXISTS") 
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
        $template_path = Template::getTemplatePath($value);
        return (File::exists($template_path) == false) ? false : true;
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
