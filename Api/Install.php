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
use Arikaim\Core\App\PostInstallActions;

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
     * Install Arikaim extensions
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function installExtensionsController($request, $response, $data) 
    {           
        $this->onDataValid(function($data) {    
            // clear cache
            $this->get('cache')->clear();

            // do install
            $install = new SystemInstall();
            $result = $install->installExtensions();   
                       
            if ($result == false) { 
                $this->addErrors($install->getErrors());
                return;
            }
            $this->message('Arikaim extensions was installed successfully.');                      
        });
        $data->validate();      
    }
    
    /**
     * Post install actions
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function postInstallActionsController($request, $response, $data) 
    {           
        $this->onDataValid(function($data) {    
            // clear cache
            $this->get('cache')->clear();

            // do post install actions
            $errors = PostInstallActions::runPostInstallActions();
                       
            if ($errors > 0) { 
                $this->error('Post install actions error');
                return;
            }
            $this->message('Post install actions completed successfully.');                      
        });
        $data->validate();      
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
