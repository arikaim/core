<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Mail;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Interfaces\Mail\MailInterface;
use Arikaim\Core\Interfaces\Mail\MailerInterface;

/**
 * Send emails
 */
class Mailer implements MailerInterface
{
    /**
     * Mailer object
     *
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * Mailer error message
     *
     * @var string
     */
    private $error;

    /**
     * Constructor
     * 
     * @param \Swift_Transport $transportDriver
     */
    public function __construct($transportDriver = null) 
    {
        $this->error = null;
        if ($transportDriver == null) {
            $transport = $this->createDefaultTransportDriver();
        }
        $this->mailer = new \Swift_Mailer($transport);
    }

    /**
     * Create default transport driver
     *
     * @return \Swift_Transport
     */
    private function createDefaultTransportDriver()
    {
        if (Arikaim::options()->get('mailer.use.sendmail') === true) {
            $transport = new \Swift_SendmailTransport('/usr/sbin/sendmail -bs');
        } else {           
            $transport = new \Swift_SmtpTransport(Arikaim::options()->get('mailer.smpt.host'),Arikaim::options()->get('mailer.smpt.port'));
            $transport->setUsername(Arikaim::options()->get('mailer.username'));
            $transport->setPassword(Arikaim::options()->get('mailer.password'));   
           
            if (Arikaim::options()->get('mailer.smpt.ssl') == true) {
                $transport->setEncryption('ssl');    
            }              
        }

        return $transport;
    }

    /**
     * Send email
     *
     * @param MailInterface $message
     * @return bool
     */
    public function send(MailInterface $message)
    {
        $this->error = null;

        $message->build();
        $mail = $message->getMessage();

        try {
            $result = $this->mailer->send($mail);
        } catch (\Exception $e) {
            //throw $th;
            $this->error = $e->getMessage();
            $result = false;
        }
        return ($result > 0) ? true : false;
    }

    /**
     * Get mailer transport
     *
     * @return \Swift_Transport
     */
    public function getTransport()
    {
        return $this->mailer->getTransport();
    }

    /**
     * Set transport driver
     *
     * @param \Swift_Transport $driver
     * @return Swift_Mailer
     */
    public function setTransport($driver)
    {
        return $this->mailer = new \Swift_Mailer($driver);
    }

    /**
     * Get error message
     *
     * @return string|null
     */
    public function getErrorMessage()
    {
        return $this->error;
    }    
}