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

/**
 * Control Panel controler
*/
class AdminApi extends ApiControler
{
    /**
     * Install Arikaim
     *
     * @param object $request
     * @param object $response
     * @param array $args
     * @return object
     */
    public function install($request, $response, $args) 
    {           
        Model::Users()->logout();
        
        $install = new Install();
        $requirements = System::checkSystemRequirements();
        $form = Form::create($request->getParsedBody()); 
        $form->addRule('database',Form::Rule()->text(2));
        $form->addRule('username',Form::Rule()->text(2));
        $form->addRule('password',Form::Rule()->text(2));

        $user_name  = $form->get('username');
        $password   = $form->get('password');
        $database   = $form->get('database');
        
        if ($form->validate() == true) { 
            // save config file               
            Arikaim::config()->setValue('db/username',$user_name);
            Arikaim::config()->setValue('db/password',$password);
            Arikaim::config()->setValue('db/database',$database);
            // save and reload config file
            Arikaim::config()->saveConfigFile();
            Arikaim::config()->loadConfig();
            
            $result = Arikaim::db()->testConnection(Arikaim::config('db'));
            if ($result == true) {          
                // do install
                $result = $install->install();     
            }          
        } else {
            $this->setApiErrors($form->getErrors());
        }
        return $this->getApiResponse();
    }

    /**
     * Update Arikaim
     *
     * @param object $request
     * @param object $response
     * @param array $args
     * @return object
     */
    public function update($request, $response, $args) 
    {           
        $this->requireControlPanelPermission();
        $update = new Update();
        $result = $update->update();
        if ($result == false) {
            $this->setApiErrors('Error update arikaim core.');
        }
        return $this->getApiResponse();
    }
    
    public function updateCheckVersion($request, $response, $args)
    {
        return $this->getApiResponse();
    }

    /**
     * Remove system logs
     *
     * @param object $request
     * @param object $response
     * @param array $args
     * @return object
     */
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
        $message = Arikaim::mailer()->messageFromTemplate($user->email,"system:admin.email-messages.test");
        $message->setFrom($user->email);
     
        $result = Arikaim::mailer()->send($message);
        if ($result == false) {
            $this->setApiError('Error send test email!');
            return $this->getApiResponse();
        }
        return $this->getApiResponse();
    }

    public function saveSettings($request, $response, $args)
    {
        $this->requireControlPanelPermission();
        
        $form = Form::create($request->getParsedBody());    
        $cors = $form->get('cors',false);
        $debug = $form->get('debug',false);
       
        Arikaim::config()->setBooleanValue('settings/cors',$cors);
        Arikaim::config()->setBooleanValue('settings/debug',$debug);
        // save and reload config file
        Arikaim::config()->saveConfigFile();
        Arikaim::config()->loadConfig();
       
        return $this->getApiResponse();
    }
}
