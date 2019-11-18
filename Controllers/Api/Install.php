<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Controllers\Api;

use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\System\Install as SystemInstall;
use Arikaim\Core\Arikaim;

/**
 * Install controller
*/
class Install extends ApiController
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
     * Install Arikaim 
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function installController($request, $response, $data) 
    {           
        Arikaim::auth()->logout();
        
        $this->onDataValid(function($data) { 
            $install = new SystemInstall();
            // save config file               
            Arikaim::config()->setValue('db/username',$data->get('username'));
            Arikaim::config()->setValue('db/password',$data->get('password'));
            Arikaim::config()->setValue('db/database',$data->get('database'));         
            Arikaim::config()->save();
             
            $result = Arikaim::db()->testConnection(Arikaim::config('db'));
            if ($result == true) {          
                // do install
                $result = $install->install();   
                $this->setResponse($result,'install','errors.install');  
            } else {
                $this->message('errors.db');
            }         
        });
        $data
            ->addRule("text:min=2","database")
            ->addRule("text:min=2","username")
            ->addRule("text:min=2","password")
            ->validate();      
    }
}
