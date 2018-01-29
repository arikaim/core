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
use Arikaim\Core\Db\Model;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Form\Form;
use Arikaim\Core\Access\Jwt;
use Arikaim\Core\View\Html\Component;

class UsersApi extends ApiControler  
{   
    public function adminLogin($request, $response, $args) {
        return $this->login($request, $response, $args);
    }

    public function login($request, $response, $args) 
    { 
        $this->form->setFields($request->getParsedBody());
        $this->form->addRule('user_name',Form::Rule()->text(2));   
        $this->form->addRule('password',Form::Rule()->text(2));  

        if ($this->form->validate() == false) {
            $this->setApiErrors($this->form->getErrors());
            return $this->getApiResponse();   
        }

        $user_name = $this->form->get('user_name');
        $password = $this->form->get('password');
        $user = Model::User();   
      
        $user_uuid = $user->login($user_name,$password);
       
        if ($user_uuid == false) {
            $this->setApiError(Arikaim::getError("LOGIN_ERROR"));   
        } else {
            // create  JWT token
            $jwt = new Jwt(); 
            $jwt->set('uuid',"'" . $user_uuid . "'");
            $jwt->set('admin','1');
           
            $token = $jwt->createToken();
            Arikaim::session()->set('token',$token);
            $this->setApiResult($token);     
        }        
        return $this->getApiResponse();   
    }

    public function logout($request, $response, $args) 
    {    
        Model::User()->logout();  
        return $this->getApiResponse();   
    }   
    
    public function isLoged($request, $response, $args)
    {
        $loged = Model::User()->isLoged();
        $this->setApiResult($loged);  
        return $this->getApiResponse();
    }

    public function changeDetails($request, $response, $args)
    {
        $messages = Component::readProperties('system:admin/settings/user-settings','messages');

        $this->form->setFields($request->getParsedBody());
        $this->form->addRule('user_name',Form::Rule()->text(2),true);   
        $this->form->addRule('email',Form::Rule()->email(),false);   
        // change password fields
        $this->form->addRule('old_password',Form::Rule()->text(5),false);
        $this->form->addRule('new_password',Form::Rule()->text(5),false);
        $this->form->addRule('repeat_password',Form::Rule()->text(5),false);
        $form = $this->form->toArray();
       
        if ($this->form->validate() == true) {
            $user = Model::User();

            // check if user name is changed 
            $loged_user = $user->getLogedUser();
            if (is_object($loged_user) == false) {
                $this->setApiError(Arikaim::getError("ACCESS_DENIED")); 
                return $this->getApiResponse();   
            }

            // check if user name exists 
            if ($loged_user->user_name != $form['user_name']) {
                $result = $user->userNameExist($form['user_name']); 
                if ($result == true) {
                    $this->setApiError($messages['user_name_exists']);    
                    return $this->getApiResponse();                 
                }
            }
            
            $loged_user->user_name = $form['user_name'];
            $loged_user->email = $form['email'];
            $result = $loged_user->update(); 
            if ($result == false) {
                $this->setApiError(Arikaim::getError("SAVE_ERROR")); 
            }
               
            // check for change password 
            if (strlen($form['old_password']) > 4) {
                if ($loged_user->isValidPassword($form['old_password']) == false) {
                    $this->setApiError($messages['invalid_password']);
                    return $this->getApiResponse();    
                } 
                if ($form['new_password'] != $form['repeat_password']) {
                    // passwords not mach
                    $this->setApiError($messages['change_password_error']);
                    return $this->getApiResponse();                      
                }
                if (strlen($form['new_password']) < 5) {
                    $this->setApiError($messages['invalid_password']);
                    return $this->getApiResponse();    
                }
                $result = $user->changePassword($loged_user->id,$form['new_password']);
                if ($result == false) {
                    $this->setApiError(Arikaim::getError("SAVE_ERROR"));
                }
            }

        } else {           
            $this->setApiErrors($this->form->getErrors());            
        }
        return $this->getApiResponse(); 
    }
}
