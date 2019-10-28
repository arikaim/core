<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Controllers\Api;

use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\Packages\Module\ModulesManager;
use Arikaim\Core\Db\Model;

/**
 * Modules controller
*/
class Modules extends ApiController
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
     * Save module config
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function saveConfigController($request, $response, $data)
    {
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) {            
            $module = Model::Modules()->FindByColumn('name',$data['name']);
            $module->config = $data->toArray();
            $result = $module->save();

            $this->setResponse($result,'modules.config','errors.modules.config');
        });
        $data->validate();       
    }

    /**
     * Uninstall module
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function unInstallModuleController($request, $response, $data)
    {
        $this->requireControlPanelPermission();
        
        $this->onDataValid(function($data) { 
            $manager = new ModulesManager();
            $result = $manager->unInstallPackage($data['name']);
            
            $this->setResponse($result,'modules.uninstall','errors.modules.uninstall');
        });
        $data->validate();    
    }

    /**
     * Update module
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function updateModuleController($request, $response, $data)
    {
        $this->requireControlPanelPermission();
        
        $this->onDataValid(function($data) { 
            $manager = new ModulesManager();
            $result = $manager->reInstallPackage($data['name']);

            $this->setResponse($result,'modules.update','errors.modules.update');
        });
        $data->validate();  
    }
    
    /**
     * Install module
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function installModuleController($request, $response, $data)
    {
        $this->requireControlPanelPermission();
        
        $this->onDataValid(function($data) {            
            $manager = new ModulesManager();
            $result = $manager->installPackage($data['name']);

            $this->setResponse($result,'modules.install','errors.modules.install');          
        });
        $data->validate();  
    }

    /**
     * Enable module
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function enableModuleController($request, $response, $data)
    {
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) {              
            $manager = new ModulesManager();
            $result = $manager->enablePackage($data['name']);

            $this->setResponse($result,'modules.enable','errors.modules.enable');       
        });
        $data->validate();         
    }

    /**
     * Disable module
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function disableModuleController($request, $response, $data)
    {
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) {             
            $manager = new ModulesManager();
            $result = $manager->disablePackage($data['name']);

            $this->setResponse($result,'modules.disable','errors.modules.disable');       
        });
        $data->validate();         
    }
}
