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
use Arikaim\Core\Access\Access;

/**
 * Users controler login, logout, change password api controler.
*/
class Users extends ApiControler  
{   
    /**
     * Control panel login
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function adminLogin($request, $response, $data) 
    {
        $valid = $data
            ->addRule('user_name',$data->rule()->text(2))   
            ->addRule('password',$data->rule()->text(2)) 
            ->validate();

        if ($valid == false) {               
            $this->setApiErrors($data->getErrors());
            return $this->getApiResponse();   
        }

        $user = Model::Users();         
        $user = $user->login($data['user_name'],$data['password']);
      
        if ($user === false) {           
            $this->setApiErrors($data->toArray());
            $this->setApiError(Arikaim::getError("LOGIN_ERROR"));   
        } else {        
            $access = Arikaim::access()->hasPermission(Access::CONTROL_PANEL,Access::FULL,$user->uuid);
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

    /**
     * Logout
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function logout($request, $response, $data) 
    {    
        Model::Users()->logout();  
        return $this->getApiResponse();   
    }   
    
    /**
     * Return login status
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function isLoged($request, $response, $data)
    {
        $loged = Model::Users()->isLoged();
        $this->setApiResult($loged);  
        return $this->getApiResponse();
    }

    /**
     * Control Panel change user details
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function changeDetails($request, $response, $data)
    {
        // access from contorl panel only 
        $this->requireControlPanelPermission();
    
        $valid = $data 
            ->addRule('user_name',$data->rule()->text(2),true) 
            ->addRule('email',$data->rule()->email(),false)
            // change password fields
            ->addRule('old_password',$data->rule()->text(5),false)
            ->addRule('new_password',$data->rule()->text(5),false)
            ->addRule('repeat_password',$data->rule()->text(5),false)
            ->validate();
       
        if ($valid == true) {

            $messages = Arikaim::view()->component()->getComponentProperties('system:admin/settings/user');

            $user_name = $data->get('user_name');
            $user = Model::Users();
            // check if user name is changed 
            $loged_user = $user->getLogedUser();
            if (is_object($loged_user) == false) {
                $this->setApiError(Arikaim::getError("ACCESS_DENIED")); 
                return $this->getApiResponse();   
            }

            // check if user name exists 
            if ($loged_user->user_name != $user_name) {
                if ($user->userNameExist($user_name) == true) {
                    $error = $messages->getByPath('messages/user_name_exists');    
                    $data->setError('user_name',$error);                                
                }
            }
            
            if ($data->isValid() == true) {
                $loged_user->user_name = $user_name;
                $loged_user->email = $data->get('email');
                $result = $loged_user->update(); 
                if ($result == false) {
                    $this->setApiError(Arikaim::getError("SAVE_ERROR")); 
                }
            }

            // check for change password 
            if (strlen($data->get('old_password')) > 4) {
                if ($loged_user->isValidPassword($data->get('old_password')) == false) {
                    $error = $messages->getByPath('messages/invalid_password');
                    $data->setError('old_password',$error);  
                } 
                if ($data->get('new_password') != $data->get('repeat_password')) {
                    // passwords not mach
                    $error = $messages->getByPath('messages/change_password_error');
                    $data->setError('new_password',$error);                       
                }
                if (strlen($data->get('new_password')) < 5) {
                    $error = $messages->getByPath('messages/invalid_password');
                    $data->setError('new_password',$error);   
                }
                if ($data->isValid() == true) {
                    $result = $user->changePassword($loged_user->id,$data->get('new_password'));
                    if ($result == false) {
                        $this->setApiError(Arikaim::getError("SAVE_ERROR"));              
                    } 
                }             
            }
        }         
        $this->setApiErrors($data->getErrors());            
        return $this->getApiResponse(); 
    }

    /**
     * Password recovery
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function passwordRecovery($request, $response, $data)
    {
        $users = Model::Users()->getControlPanelUser();
        if ($users == false) {
            $this->setApiError('Missing control panel user');
            return $this->getApiResponse(); 
        }
        
        $messages = Arikaim::view()->component()->getComponentProperties('system:admin/password-recovery');
        $error = $messages->getByPath('errors/email');

      
        $valid = $data
            ->addRule('email',$data->rule()->equal($users->email,$error))
            ->addRule('email',$data->rule()->email($error));

        if ($valid== true) {
            // create access code          
            $access_key = $users->createAccessKey($users->uuid);
            if ($access_key === false) {
                $error = $messages->getByPath('errors/access_key');
                $this->setApiError($error);
                return $this->getApiResponse();
            }
            // send email
            $params = ['access_key' => $access_key];
            $message = Arikaim::mailer()->messageFromTemplate($users->email,"system:admin.email-messages.password-recovery",$params);
            $message->setFrom($users->email);
            
            $result = Arikaim::mailer()->send($message);
            if ($result == false) {
                $error = $messages->getByPath('errors/send');
                $this->setApiError($error);
                return $this->getApiResponse();
            }
        }
        $this->setApiErrors($data->getErrors());
        return $this->getApiResponse(); 
    }
}
