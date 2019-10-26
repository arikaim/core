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

use Arikaim\Core\Interfaces\Mail\MailInterface;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Utils\StaticFacade;
use Arikaim\Core\View\Html\HtmlComponent;

/**
 * Mail base class
 */
class Mail implements MailInterface
{ 
    /**
     * Message
     *
     * @var Swift_Message
     */
    protected $message;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->message = new \Swift_Message();
        $this->setDefaultFrom();
    } 

    /**
     * Set default from field
     *    
     * @return Mail
     */
    public function setDefaultFrom()
    {
        $from = Arikaim::options()->get('mailer.from.email',null);
        $fromName = Arikaim::options()->get('mailer.from.name',null);
        if (empty($from) == false) {
            $this->from($from,$fromName);
        }
        return $this;
    }

    /**
     * Create mail
     *
     * @return Mail
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Send email to 
     *
     * @param string|array $email
     * @param string|null $name
     * @return bool
     */
    public static function sendTo($email, $name = null)
    {
        $mail = new static();
        return $mail->to($email,$name)->send();
    }

    /**
     * Return facade class name
     *
     * @return string
     */
    public static function getInstanceClass()
    {
        return "Arikaim\Core\Mail\Mail";
    }

    /**
     * Build email
     *
     * @return Mial
     */
    public function build()
    {
        return $this;
    }
  
    /**
     * Set email subject
     *
     * @param string $subject
     * @return Mail
     */
    public function subject($subject)
    {
        $this->message->setSubject($subject);
        return $this;
    }

    /**
     * Attach file
     *
     * @param string $file
     * @return Mail
     */
    public function attach($file)
    {
        $attachment = Swift_Attachment::fromPath($file);
        $this->message->attach($attachment);
        return $this;
    }

    /**
     * Set from
     *
     * @param string|array $email
     * @param string|null $name
     * @return Mail
     */
    public function from($email, $name = null)
    {
        $this->message->setFrom($email,$name);
        return $this;
    } 

    /**
     * Set to
     *
     * @param string|array $email
     * @param string|null $name
     * @return Mail
     */
    public function to($email, $name = null)
    {        
        $this->message->setTo($email,$name);        
        return $this;
    }

    /**
     * Set reply to
     *
     * @param string|array $email
     * @param string|null $name
     * @return Mail
     */
    public function replyTo($email, $name = null)
    {
        $this->message->setReplyTo($email,$name);
        return $this;
    }

    /**
     * Set cc
     *
     * @param string|array $email
     * @param string|null $name
     * @return Mail
     */
    public function cc($email, $name = null)
    {
        $this->message->setCc($email,$name);
        return $this;
    }

    /**
     * Set bcc
     *
     * @param string|array $email
     * @param string|null $name
     * @return Mail
     */
    public function bcc($email, $name = null)
    {
        $this->message->setBcc($email,$name);
        return $this;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     * @return Mail
     */
    public function priority($priority = 3)
    {
        $this->message->setPriority($priority);
        return $this;
    }
    
    /**
     * Set email body
     *
     * @param string $message
     * @return Mail
     */
    public function message($message)
    {
        $this->message->setBody($message);
        return $this;
    }

    /**
     * Set email content type
     *
     * @param string $type
     * @return Mail
     */
    public function contentType($type = "text/plain")
    {
        $this->message->setContentType($type);
        return $this;
    }

    /**
     * Return message body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->message->getBody();
    }

    /**
     * Get message instance
     *
     * @return Swift_Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Load email message from html component
     *
     * @param string $componentName
     * @param array $params
     * @return object
     */
    public function loadComponent($componentName, $params = [])
    {
        $component = HtmlComponent::renderComponent($componentName,$params,null,false);
        $properties = $component->getProperties();
        $body = $component->getHtmlCode();

        $this->message($body);

        if (Utils::hasHtml($body) == true) {
            $this->contentType('text/html');
        } else {
            $this->contentType('text/plain');
        }
        
        // subject
        $subject = (isset($properties['subject']) == true) ? $properties['subject'] : "";
        if (empty($subject) == false) {
            $this->subject($subject);
        }
    
        return $this;
    }

    /**
     * Send email
     *
     * @return bool
     */
    public function send() 
    {
        return Arikaim::mailer()->send($this);
    }

    /**
     * Get error message
     *
     * @return string
     */
    public static function getError()
    {
        return  Arikaim::mailer()->getErrorMessage();
    }
}
