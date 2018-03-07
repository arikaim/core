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
use Arikaim\Core\System\Install;
use Arikaim\Core\System\Update;
use Arikaim\Core\System\System;
use Arikaim\Core\Form\Form;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Module\ModulesManager;

class AdminApi extends ApiControler
{
    public function install($request, $response, $args) 
    {           
        Model::Users()->logout();
        
        $install = new Install();
        $requirements = System::checkSystemRequirements();
    
        $this->form->setFields($request->getParsedBody());     
        $this->form->addRule('database',Form::Rule()->text(2));
        $this->form->addRule('username',Form::Rule()->text(2));
        $this->form->addRule('password',Form::Rule()->text(2));

        $user_name = $this->form->get('username');
        $password = $this->form->get('password');
        $database = $this->form->get('database');
        
        if ($this->form->validate() == true) { 
            $result = $install->testDbConnection($user_name,$password);
            if ($result == true) {
                // save config file               
                Arikaim::config()->setValue('db/username',$user_name);
                Arikaim::config()->setValue('db/password',$password);
                Arikaim::config()->setValue('db/database',$database);
                // save config file
                Arikaim::config()->saveConfigFile();
                Arikaim::config()->loadConfig();
                // do install
                $install->install();               
            }           
        } else {
            $this->setApiErrors($this->form->getErrors());
        }
        return $this->getApiResponse();
    }

    public function update($request, $response, $args) 
    {           
        $this->requireControlPanelPermission();
        $update = new Update();
        $result = $update->update();
        return $this->getApiResponse();
    }
    
    public function updateCheckVersion($request, $response, $args)
    {
        return $this->getApiResponse();
    }

    public function clearLogs($request, $response, $args)
    {
        $this->requireControlPanelPermission();
        $result = Arikaim::logger()->deleteSystemLogs();
        if ($result == false) {
            $this->setApiErrors(Arikaim::errors()->getError("DELETE_FILE_ERROR"));
        }
        return $this->getApiResponse();
    }

    public function deleteQueueWorkerJobs($request, $response, $args)
    {
        $this->requireControlPanelPermission();
        
        Arikaim::jobs()->getQueueService()->removeAllJobs();
        return $this->getApiResponse();
    }
    
    public function updateQueueWorkerJobs($request, $response, $args)
    {
        $this->requireControlPanelPermission();
        Arikaim::jobs()->update();
        return $this->getApiResponse();
    }

    public function updateModules($request, $response, $args)
    {
        $this->requireControlPanelPermission();
        $modules = new ModulesManager();
        $result = $modules->install();
        return $this->getApiResponse();
    }

    public function sendTestEmail($request, $response, $args)
    {
        $this->requireControlPanelPermission();
        $user = Model::Users()->getLogedUser();
        if ($user == false) {
            $this->setApiErrors('Not loged in!');
            return $this->getApiResponse();
        }

        if (Utils::isEmail($user->email) == false) {
            $this->setApiErrors('Control panel user email not valid!');
            return $this->getApiResponse();
        }       
        $message = Arikaim::mailer()->messageFromTemplate($user->email,"system:admin.email-messges.test",[],$user->email);
        $result = Arikaim::mailer()->send($message);
        if ($result == false) {
            $this->setApiErrors('Error send test email!');
            return $this->getApiResponse();
        }
        return $this->getApiResponse();
    }
}
