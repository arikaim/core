<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\Mail\Mail;

use Arikaim\Core\Mail\Mail;

/**
 * Test mail
 */
class TestMail extends Mail
{ 
    /**
     * Build email
     *
     * @return void
     */
    public function build()
    {
        $this->loadComponent('system:admin.emails.test');
        
        return $this;
    }
}
