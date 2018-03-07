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
use Arikaim\Core\Form\Form;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Extension\ExtensionsManager;
use Arikaim\Core\Arikaim;

class ExtensionsApi extends ApiControler
{
    
    public function update($request, $response, $args)
    {
        $this->requireControlPanelPermission();
        return $this->install($request, $response, $args);
    }

    public function changeStatus($request, $response, $args)
    {
        $this->requireControlPanelPermission();

        $extension_manager = new ExtensionsManager();
        $this->form->addRule('status',Form::Rule()->checkList([0,1,'toggle']));
        $this->form->addRule('name',Form::Rule()->extensionPath($args['name']));

        if ($this->form->validate($args) == true) {
            try {
                $name = $this->form->get('name');  
                $result['status'] = $this->form->get('status');                      
                if ($result['status'] == 'toggle') {  
                    $extension = Model::Extensions()->where('name','=',$name)->first();                          
                    $result['status'] = ($extension->status == 1) ? 0 : 1;
                } 
                if ($result['status'] == 1) {
                    $extension_manager->enable($name);
                } else {
                    $extension_manager->disable($name);
                }
                $this->setApiResult($result);
            } catch(\Exception $e) {
                $this->setApiError($e->getMessage());
            }
        } else {
            $this->setApiErrors($this->form->getErrors());
        }
        return $this->getApiResponse();
    }

    public function unInstall($request, $response, $args)
    {
        $this->requireControlPanelPermission();

        $this->form->addRule('name',Form::Rule()->extensionPath($args['name']));
   
        if ($this->form->validate($args) == true) {
            $extension_manager = new ExtensionsManager();
            $result = $extension_manager->unInstall($args['name']);
            if ($result == false) {
                $this->setApiError( Arikaim::getError("SYSTEM_ERROR",['extension_name' => "$extension_name"]) );
            }              
        } else {
            $this->setApiError(Arikaim::getError("EXTENSION_NOT_EXISTS"));
        }
        return $this->getApiResponse();   
    }

    public function install($request, $response, $args)    
    {       
        $this->requireControlPanelPermission();

        $this->form->addRule('name',Form::Rule()->extensionPath($args['name']));
    
        if ($this->form->validate($args) == true) {
            $extension_manager = new ExtensionsManager();
            $result = $extension_manager->install($args['name']);
            if ($result == false) {
                $this->setApiError(Arikaim::getError("EXTENSION_INSTALL_ERROR",['extension_name' => "$extension_name"]) );
            }
        } else {
            $this->setApiError(Arikaim::getError("EXTENSION_NOT_EXISTS"));
        }        
        return $this->getApiResponse();   
    }
}
