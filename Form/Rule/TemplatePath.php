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

class TemplatePath extends AbstractRule
{  
    protected $extension_name;

    public function __construct($extension_name, $error_code = "TEMPLATE_NOT_EXISTS") 
    {
        parent::__construct(null,null,$error_code);
        $this->extension_name = $extension_name; 
    }

    public function customFilter($value) 
    {           
        $template_path = Template::getTemplatePath($value);
        if (File::exists($template_path) == false) {           
            $this->setError();
        } 
        return $this->isValid();
    } 

    public function getFilter()
    {       
        return FILTER_CALLBACK;
    }

    public function getFilterOptions()
    {
        return $this->getCustomFilterOptions();
    }
}
