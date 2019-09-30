<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Interfaces\Mail;

/**
 * Mail interface
 */
interface MailInterface
{   
    /**
     * Build email
     *
     * @return MailInterface
     */ 
    public function build();

    /**
     * Get Swift_Message message instance
     *
     * @return Swift_Message
     */
    public function getMessage();
}
