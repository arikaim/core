<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */

namespace Arikaim\Core\Form\Rule;


use Arikaim\Core\Form\AbstractRule;
use Arikaim\Core\Extension\ExtensionsManager;
use Arikaim\Core\Utils\File;

class ExtensionPath extends AbstractRule
{  
    protected $extension_name;

    public function __construct($extension_name) 
    {
        parent::__construct();
        $this->extension_name = $extension_name; 
    }

    public function customFilter($value) 
    {           
        $path = ExtensionsManager::getExtensionsPath();
        $extension_path = $path . $value;
        if (File::exists($extension_path) == false ) {           
            $this->setError("EXTENSION_NOT_EXISTS");
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
