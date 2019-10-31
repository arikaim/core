<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Mails;

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
