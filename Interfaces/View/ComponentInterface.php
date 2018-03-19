<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Interfaces\View;

interface ComponentInterface 
{  
    public function hasError();
    public function hasContent();
    public function hasProperties();
    public function hasFiles($file_type = null);
    public function getFiles($file_type = null);
    public function getProperties();
    public function getOptions();
    public function getName();
    public function getType();
    public function isValid();
}
