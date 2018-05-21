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
use Arikaim\Core\Access\Access;

/**
 * Users controler login, logout, change password api controler.
*/
class UsersApi extends ApiControler  
{   
    public function adminLogin($request, $response, $args) 
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
        $user = Model::Users();         
        $user = $user->login($user_name,$password);
       
        if ($user === false) {
            $this->setApiError(Arikaim::getError("LOGIN_ERROR"));   
        } else {        
            $access = Arikaim::access()->hasPermission(Access::CONTROL_PANEL,Access::FULL,$user->id);
            if ($access == true) {
                // create JWT token
                $token = Arikaim::access()->createToken($user->id,$user->uuid);            
                $this->setApiResult($token);
            } else {
                $this->setApiError(Arikaim::getError("LOGIN_ERROR"));   
            }     
        }        
        return $this->getApiResponse();   
    }

    public function logout($request, $response, $args) 
    {    
        Model::Users()->logout();  
        return $this->getApiResponse();   
    }   
    
    public function isLoged($request, $response, $args)
    {
        $loged = Model::Users()->isLoged();
        $this->setApiResult($loged);  
        return $this->getApiResponse();
    }

    public function changeDetails($request, $response, $args)
    {
        // access from contorl panel only 
        $this->requireControlPanelPermission();
        
        $messages = Arikaim::view()->component()->getComponentProperties('system:admin/settings/user');
     
        $this->form->setFields($request->getParsedBody());
        $this->form->addRule('user_name',Form::Rule()->text(2),true);   
        $this->form->addRule('email',Form::Rule()->email(),false);   
        // change password fields
        $this->form->addRule('old_password',Form::Rule()->text(5),false);
        $this->form->addRule('new_password',Form::Rule()->text(5),false);
        $this->form->addRule('repeat_password',Form::Rule()->text(5),false);
        $form = $this->form->toArray();
       
        if ($this->form->validate() == true) {
            $user = Model::Users();
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
                    $error = $messages->getByPath('messages/user_name_exists');    
                    $this->form->setError('user_name',$error);                                
                }
            }

            if ($this->form->isValid() == true) {
                $loged_user->user_name = $form['user_name'];
                $loged_user->email = $form['email'];
                $result = $loged_user->update(); 
                if ($result == false) {
                    $this->setApiError(Arikaim::getError("SAVE_ERROR")); 
                }
            }
               
            // check for change password 
            if (strlen($form['old_password']) > 4) {
                if ($loged_user->isValidPassword($form['old_password']) == false) {
                    $error = $messages->getByPath('messages/invalid_password');
                    $this->form->setError('old_password',$error);  
                } 
                if ($form['new_password'] != $form['repeat_password']) {
                    // passwords not mach
                    $error = $messages->getByPath('messages/change_password_error');
                    $this->form->setError('new_password',$error);                       
                }
                if (strlen($form['new_password']) < 5) {
                    $error = $messages->getByPath('messages/invalid_password');
                    $this->form->setError('new_password',$error);   
                }
                if ($this->form->isValid() == true) {
                    $result = $user->changePassword($loged_user->id,$form['new_password']);
                    if ($result == false) {
                        $this->setApiError(Arikaim::getError("SAVE_ERROR"));              
                    }
                }                
            }
        }         

        $this->setApiErrors($this->form->getErrors());            
        return $this->getApiResponse(); 
    }

    public function passwordRecovery($request, $response, $args)
    {
        $users = Model::Users();
        $admin_email = $users->getControlPanelUserEmail();
        $messages = Arikaim::view()->component()->getComponentProperties('system:admin/password-recovery');
        $error = $messages->getByPath('errors/email');
      
        $this->form->setFields($request->getParsedBody());

        $this->form->addRule('email',Form::Rule()->equal($admin_email,$error));
        $this->form->addRule('email',Form::Rule()->email($error));

        if ($this->form->validate() == true) {
            // create access code
            $user_uuid = $users->getControlPanelUser();
            $access_key = $users->createAccessKey($user_uuid);
            if ($access_key === false) {
                $error = $messages->getByPath('errors/access_key');
                $this->setApiError($error);
                return $this->getApiResponse();
            }
            // send email
            $params = ['access_key' => $access_key];
            $message = Arikaim::mailer()->messageFromTemplate($admin_email,"system:admin.email-messages.password-recovery",$params);
            $message->setFrom($admin_email);
            
            $result = Arikaim::mailer()->send($message);
            if ($result == false) {
                $error = $messages->getByPath('errors/send');
                $this->setApiError($error);
                return $this->getApiResponse();
            }

        }
        $this->setApiErrors($this->form->getErrors());
        return $this->getApiResponse(); 
    }
}
