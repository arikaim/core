<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System\Console;

class ConsoleHelper
{  
    public static function getLabelText($text, $color = 'green')
    {
        return "<fg=$color>$text</>";
    }

    public static function getStatusText($status)
    {
        return ($status == 1) ? "<fg=green>Enabled</>" : '<fg=red>Disabled</>';
    }

    public static function getYesNoText($value)
    {
        $value = (bool)$value; 
        return ($value == true) ? "<fg=green>Yes</>" : '<fg=red>No</>';
    }

    public static function getDescriptionText($description)
    {
        return "<fg=cyan>$description</>";
    }
}
