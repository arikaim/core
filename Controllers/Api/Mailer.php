<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Controllers\Api;

use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\System\Mails\TestMail;

/**
 * Mailer controller
*/
class Mailer extends ApiController
{
    /**
     * Init controller
     *
     * @return void
     */
    public function init()
    {
        $this->loadMessages('system:admin.messages');
    }

    /**
     * Send test email
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function sendTestEmailController($request, $response, $data)
    {
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) { 
            
            $user = Arikaim::auth()->getUser();
            if (Utils::isEmail($user->email) == false) {
                $this->setError('Control panel user email not valid!');
                return $this->getResponse();
            }       
    
            $result = TestMail::create()
                ->to($user->email,'Admin User')
                ->from($user->email,'Arikaim CMS')
                ->send();
            
            $this->setResponse($result,'mailer.send','errors.mailer.test');           
        });
        $data->validate();
    }
}
