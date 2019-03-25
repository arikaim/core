<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System;

use Arikaim\Core\System\System;

class PhpError
{
    public static function show($errno, $errstr, $errfile, $errline)
    {
        $type = Self::getErrorTypeLabel($errno);
        $output = (System::isConsole() == true) ? Self::getConsoleOutput($type,$errstr, $errfile, $errline) :  Self::getHtmlOutput($type,$errstr, $errfile, $errline);
        
        echo $output;
    }

    public static function getConsoleOutput($type,$errstr, $errfile, $errline)
    {
        $output = "\n";
        $output .= "Error: $type  $errstr\n";
        $output .= "File: $errfile Line: $errline\n";
        return $output;
    }

    public static function getHtmlOutput($type,$errstr, $errfile, $errline)
    {
        $title = 'Error';       
        $html = "<b>$type</b> $errstr <br>"; 
        $html .= "<b>$errfile</b> Line: <b>$errline</b> <br>";
        $output = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
            "<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana," .
            "sans-serif;}</style></head><body><h2>$title</h2>$html</body></html>";
        return $output;
    }

    public static function getErrorTypeLabel($errno)
    {
        switch ($errno) {
            case E_USER_ERROR:
                return "USER ERROR";
            case E_USER_WARNING:
                return "WARNING";
            case E_USER_NOTICE:
                return "NOTICE";
            default:
                return "UNKNOW";
        }
    }
}
