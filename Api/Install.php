<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Api;

use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\App\Install as SystemInstall;

/**
 * Install controller
*/
class Install extends ApiController
{
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
        $this->get('access')->logout();
        
        $this->onDataValid(function($data) {    
            // clear cache
            $this->get('cache')->clear();

             
            // save config file               
            $this->get('config')->setValue('db/username',$data->get('username'));
            $this->get('config')->setValue('db/password',$data->get('password'));
            $this->get('config')->setValue('db/database',$data->get('database')); 

            $result = $this->get('config')->save();
            if ($result === false) {
                $this->error('Config file is not writtable.');
                return;
            }
            // clear cache
            $this->get('cache')->clear();

            $result = $this->get('db')->testConnection($this->get('config')->get('db'));
            if ($result == false) {                
                $this->error('Not valid database connection username or password.');
                return; 
            }

            // do install
            $install = new SystemInstall();
            $result = $install->install();   
            
            $result = ($result == false) ? SystemInstall::isInstalled() : true;
            if ($result == false) { 
                $this->addErrors($install->getErrors());
                return;
            }
            $this->message('Arikaim CMS was installed successfully.');                      
        });
        $data
            ->addRule("text:min=2","database")
            ->addRule("text:min=2","username")
            ->addRule("text:min=2","password")
            ->validate();      
    }

    /**
     * Repair installation Arikaim 
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function repairController($request, $response, $data) 
    {
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) {  
            // clear cache
            $this->get('cache')->clear();
             
            $install = new SystemInstall();
            $result = $install->install();   
            $result = ($result == false) ? SystemInstall::isInstalled() : true;
            if ($result == false) {  
                $this->addErrors($install->getErrors());
                return;
            }
            $this->message('Arikaim CMS was installed successfully.');                 
        });
        $data->validate();  
    }
}
