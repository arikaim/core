<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Controlers\Api;

use Arikaim\Core\Controlers\ApiControler;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Db\Model;


/**
 * Control Panel controler
*/
class Mailer extends ApiControler
{
    public function sendTestEmail($request, $response, $data)
    {
        $this->requireControlPanelPermission();

        $user = Model::Users()->getLogedUser();
        if ($user == false) {
            $this->setApiError('Not loged in!');
            return $this->getApiResponse();
        }

        if (Utils::isEmail($user->email) == false) {
            $this->setApiError('Control panel user email not valid!');
            return $this->getApiResponse();
        }       
        $message = Arikaim::mailer()->messageFromTemplate($user->email,"system:admin.email-messages.test");
        $message->setFrom($user->email);
     
        $result = Arikaim::mailer()->send($message);
        if ($result == false) {
            $this->setApiError('Error send test email!');
            return $this->getApiResponse();
        }
        return $this->getApiResponse();
    }
}
