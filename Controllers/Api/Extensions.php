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
use Arikaim\Core\Db\Model;
use Arikaim\Core\Packages\Extension\ExtensionsManager;
use Arikaim\Core\Arikaim;

/**
 * Extensions Api controller
*/
class Extensions extends ApiController
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
     * Update (reinstall) extension
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function updateController($request, $response, $data)
    {
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) {            
            $name = $data->get('name');
            $manager = new ExtensionsManager();
            $result = $manager->reInstallPackage($name);

            $this->setResponse($result,function() use($name) {
                $this
                    ->message('extensions.update')
                    ->field('name',$name);
            },'errors.extensions.update');
        });
        $data->validate();
    }

    /**
     * Enable/Disable extension
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function setStatusController($request, $response, $data)
    {
        $this->requireControlPanelPermission();
        
        $this->onDataValid(function($data) {               
            $manager = new ExtensionsManager();
            $name = $data->get('name');  
            $status = $data->get('status',1);      

            $result = ($status == 1) ? $manager->enablePackage($name) : $manager->disablePackage($name);
            
            $this->setResponse($result,function() use($name,$status) {
                if ($status == 1) {
                    $this->message('extensions.enable');
                } else {                 
                    $this->message('extensions.disable');
                }
                $this
                    ->field('name',$name)
                    ->field('status',$status);
            },'errors.extensions.status');
        });
        $data->validate();
    }

    /**
     * Uninstall extension
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function unInstallController($request, $response, $data)
    {
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) { 
            $manager = new ExtensionsManager();
            $name = $data->get('name');
            $result = $manager->unInstallPackage($name);

            $this->setResponse($result,function() use($name) {
                $this
                    ->message('extensions.uninstall')
                    ->field('name',$name);
            },'errors.extensions.uninstall');
        });
        $data
            ->addRule("exists:model=Extensions|field=name","name")
            ->validate();
    }

    /**
     * Install extension
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function installController($request, $response, $data)    
    {       
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) { 
            $manager = new ExtensionsManager();
            $name = $data->get('name');
            $result = $manager->installPackage($name);

            $this->setResponse($result,function() use($name) {
                $this
                    ->message('extensions.install')
                    ->field('name',$name);
            },'errors.extensions.install');            
        });
        $data
            ->addRule("extensionPath","name")
            ->validate();          
    }
}
