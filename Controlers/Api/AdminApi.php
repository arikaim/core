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
use Arikaim\Core\System\Config;
use Arikaim\Core\Form\Form;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;

class AdminApi extends ApiControler
{
    public function install($request, $response, $args) 
    {           
        Model::User()->logout();
        
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
        $update = new Update();
        $result = $update->update();
        $this->setApiResult("updated");
        return $this->getApiResponse();
    }
    
    public function updateCheckVersion($request, $response, $args)
    {
        return $this->getApiResponse();
    }

    public function clearLogs($request, $response, $args)
    {
        $result = Arikaim::logger()->deleteSystemLogs();
        if ($result == false) {
            $this->setApiErrors(Arikaim::errors()->getError("DELETE_FILE_ERROR"));
        }
        return $this->getApiResponse();
    }
}
