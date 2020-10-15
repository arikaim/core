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
        //$this->get('access')->logout();
        
        $this->onDataValid(function($data) {    
            // clear cache
            $this->get('cache')->clear();
         
            $disabled = $this->get('config')->getByPath('settings/disableInstallPage',false);
            if ($disabled == true) {
                $this->error('Install page is disabled.');
                return;
            }

            $result = SystemInstall::setConfigFilesWritable();              
            if ($result === false) {
                $this->error('Config files is not writtable.');
                return;
            }
            
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
            $this->setResponse($result,function() {
                $this
                    ->message('Arikaim CMS was installed successfully.');                    
            },'Installation error');                       
        });
        $data
            ->addRule('text:min=2','database')
            ->addRule('text:min=2','username')
            ->addRule('text:min=2','password')
            ->validate();      
    }

    /**
     * Install Arikaim modules
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function installModulesController($request, $response, $data) 
    {           
        $this->onDataValid(function($data) {    
            // clear cache
            $this->get('cache')->clear();

            // do install
            $install = new SystemInstall();
            $result = $install->installModules();   

            // clear cache
            $this->get('cache')->clear();

            $this->setResponse($result,function() {
                $this
                    ->message('Modules was installed successfully.');                    
            },'Error install modules.');                          
        });
        $data->validate();      
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

            // clear cache
            $this->get('cache')->clear();

            $this->setResponse($result,function() {
                $this
                    ->message('Extensions was installed successfully.');                    
            },'Error install extensions.');                          
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

            // clear cache
            $this->get('cache')->clear();
            
            $this->setResponse(($errors == 0),function() {
                $this
                    ->field('complete','Arikaim was installed successfully.')
                    ->message('Post install actions completed successfully.');                    
            },'Post install actions error.');                                   
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
            // run post install actions
            PostInstallActions::runPostInstallActions();

            // clear cache
            $this->get('cache')->clear();

            $this->setResponse($result,function() {
                $this
                    ->message('Arikaim CMS repair installation successfully.');                    
            },'Repair installation error.');    
        });
        $data->validate();  
    }
}
