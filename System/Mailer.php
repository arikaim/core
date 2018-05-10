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

/**
 * Send emails
 */
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
                $from = Arikaim::options()->get('mailer.from.email',null);
            }
            $message->setContentType('text/plain');
            if (empty($from) == false) {
                $message->setFrom($from,$from_name);
            } 
            $message->setTo($to);
            $message->setReplyTo($to);
            if (empty($to) == true) {
                Arikaim::errors()->addError("SYSTEM_ERROR",['details' => 'Missing email to']);
            }
        } catch(\Exception $e) {
            Arikaim::errors()->addError("SYSTEM_ERROR",['details' => $e->getMessage()]);
        }
        return $message;
    }

    public function messageFromTemplate($to, $component_name, $params = [])
    {
        $message = $this->createMessage($to);
      
        $component = Arikaim::view()->component()->render($component_name,$params,null,false);
        $properties = $component->getProperties();
        $body = $component->getHtmlCode();

        $message->setBody($body);
        if (Utils::hasHtml($body) == true) {
            $message->setContentType('text/html');
        }
        $subject = (isset($properties['subject']) == true) ? $properties['subject'] : "";
        $message->setSubject($subject);
        if (empty($subject) == true) {
            Arikaim::errors()->addError("SYSTEM_ERROR",['details' => 'Missing email subject']);
        }
        if (empty($body) == true) {
            Arikaim::errors()->addError("SYSTEM_ERROR",['details' => 'Missing email body']);
        }
        return $message;
    }
}