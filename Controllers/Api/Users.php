<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Controllers\Api;

use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Arikaim;

/**
 * Users controller login, logout, change password api controller. // TODO
*/
class Users extends ApiController  
{   
    /**
     * Init controller
     *
     * @return void
     */
    public function init()
    {
        $this->loadMessages('system:admin.messages.user');
    }

    /**
     * Control panel login
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function adminLogin($request, $response, $data) 
    {
        $this->onDataValid(function($data) {  
            $credentials = [
                    'user_name' => $data->get('user_name'),
                    'password' => $data->get('password')
            ];
            $result = Arikaim::auth()->authenticate($credentials);
      
            if ($result === false) {           
                $this->error('errors.login');   
            } else {        
                $access = Arikaim::access()->hasControlPanelAccess();
                if ($access == false) {
                    $this->setError('errors.login');   
                } 
            }              
        });
        $data
            ->addRule("text:min=2","user_name")   
            ->addRule("text:min=2","password") 
            ->validate();
    
        return $this->getResponse();   
    }

    /**
     * Logout
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function logoutController($request, $response, $data) 
    {    
        Arikaim::auth()->logout();        
    }   

    /**
     * Control Panel change user details
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function changeDetails($request, $response, $data)
    {
        // access from contorl panel only 
        $this->requireControlPanelPermission();
         
        $this->onDataValid(function($data) { 
           
            $userName = $data->get('user_name');
            $logedUser = Arikaim::auth()->getUser();
            $user = Model::Users();

            // check if user name is changed           
            if ($logedUser->user_name != $userName) {
                // check if user name exists 
                if ($user->userNameExist($userName) == true) {
                    return $this->error('errors.username');                                              
                }
            }
            $logedUser->user_name = $userName;
            $logedUser->email = $data->get('email');
            $result = $logedUser->update(); 
            if ($result == false) {
                return $this->error('errors.update');                    
            }

             // check for change password 
             $password = $data->get('password',null);
             if (empty($password) == false) {
                $newPassword = $data->get('new_password');
                $repeatPassword = $data->get('repeat_password');

                if ($logedUser->verifyPassword($password) == false) {                  
                    return $this->error('errors.invalid');                  
                } 
                if ($newPassword != $repeatPassword) {
                    // passwords not mach            
                    return $this->error('errors.password');                                   
                }
              
                $result = $user->changePassword($logedUser->id,$newPassword);
                $this->setResponse($result,'update','errors.update');   
            } else {
                $this->setResponse($result,'update','errors.update'); 
            }
        });
        $data 
            ->addRule("text:min=2|required","user_name") 
            ->addRule("email","email")           
            ->addRule("text:min=5","old_password")
            ->addRule("text:min=5","new_password")
            ->addRule("text:min=5","repeat_password")
            ->validate();

        return $this->getResponse();    
    }

    /**
     * Control Panel password recovery
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function passwordRecoveryController($request, $response, $data)
    {
        $user = Model::Users()->getControlPanelUser();
        if ($user == false) {
            return $this->error('Missing control panel user')->getResponse();           
        }
        
        $this->onDataValid(function($data) use($user) { 
           
           
        });

        $data
            ->addRule("equal:value=" . $user->email,"email")
            ->addRule("email","email")
            ->validate();      
    }
}
