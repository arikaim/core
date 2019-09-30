<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Validator\Filter;

use Arikaim\Core\Validator\Filter;
use  Arikaim\Core\Utils\Html;

/**
 * SanitizeRequest filter
 */
class Sanitize extends Filter
{  
    /**
     * Filter value, return filtered value
     *
     * @param mixed $value
     * @return mixed
     */
    public function filterValue($value) 
    {      
        $value = Html::removeTags($value,['script','iframe','style','embed','applet']);
        $value = htmlspecialchars($value,ENT_HTML5 | ENT_QUOTES);
        
        return $value;
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
