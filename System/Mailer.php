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

use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Utils;

class Mailer 
{
    private $mailer;

    public function __construct() 
    {
        $this->init();
    }

    public function init()
    {
        if (Arikaim::options('mailer.use.sendmail') == true) {
            $transport = new \Swift_SendmailTransport('/usr/sbin/sendmail -bs');
        } else {
            $transport = new \Swift_SmtpTransport(Arikaim::options('mailer.smpt.host'),Arikaim::options('mailer.smpt.port'));
            $transport->setUsername(Arikaim::options('mailer.username'));
            $transport->setPassword(Arikaim::options('mailer.password'));            
        }
        $this->mailer = new \Swift_Mailer($transport);
    }

    public function send($message, $failed_recipients = null)
    {
        return $this->mailer->send($message,$failed_recipients);
    }

    public function getTransport()
    {
        return $this->mailer->getTransport();
    }

    public function createMessage($to, $from = null, $from_name = null)
    {
        $message = new \Swift_Message();
        try {
            if ($from == null) {
                $from = Arikaim::options('mailer.from.email');
            }
            $message->setContentType('text/plain');
            if (empty($from) == false) {
                $message->setFrom($from,$from_name);
            }
            $message->setTo($to);
        } catch(\Exception $e) {
            Arikaim::errors()->addErrorMessage("NOT_VALID_EMAIL",$e->getMessage());
        }
        return $message;
    }

    public function messageFromTemplate($to, $html_component_name, $params = [], $from = null, $from_name = null)
    {
        $message = $this->createMessage($to,$from,$from_name);
        $code = Arikaim::view()->component()->load($html_component_name,$params);
        $data = json_decode($code,true);

        if (isset($data['body']) == true) {
            $message->setBody($data['body']);
            if (Utils::hasHtml($data['body']) == true) {
                $message->setContentType('text/html');
            }
        }
        if (isset($data['subject']) == true) {
            $message->setSubject($data['subject']);
        }
        if (isset($data['to']) == true) {
            $message->addTo($data['to'],$data['to']['name']);
        }
        return $message;
    }
}