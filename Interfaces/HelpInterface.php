<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Interfaces;

/**
 * Help interface
 */
interface HelpInterface
{
    /**
     * Get help component name
     *
     * @return string|null
     */
    public function getHelpComponentName(); 

    /**
     * Get help page name
     *
     * @return string|null
     */
    public function getHelpPageName(); 

    /**
     * Get external help page url 
     *
     * @return string|null
     */
    public function getHelpUrl(); 
}
