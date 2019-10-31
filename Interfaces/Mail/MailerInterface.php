<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Interfaces\Mail;

use Arikaim\Core\Interfaces\Mail\MailInterface;

/**
 * Mail interface
 */
interface MailerInterface
{   
    /**
     * Send email
     *
     * @return bool
     */ 
    public function send(MailInterface $message);

     /**
     * Get error message
     *
     * @return string|null
     */
    public function getErrorMessage();
}
