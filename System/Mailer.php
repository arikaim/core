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
            Arikaim::errors()->addError("SYSTEM_ERROR",['details' => $e->getMessage()]);
        }
        return $message;
    }

    public function messageFromTemplate($to, $component_name, $params = [])
    {
        $message = $this->createMessage($to);
        $body = Arikaim::view()->component()->load($component_name,$params);
        $properties = Arikaim::view()->component()->getComponentProperties($component_name);
      
        $message->setBody($body);
        if (Utils::hasHtml($body) == true) {
            $message->setContentType('text/html');
        }
        
        if (isset($properties['subject']) == true) {
            $message->setSubject($properties->get('subject'));
        }
        return $message;
    }
}