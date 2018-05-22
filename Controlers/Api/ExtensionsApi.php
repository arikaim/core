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

/**
 * Extensions Api controler
*/
class ExtensionsApi extends ApiControler
{
    
    /**
     * Update extension
     *
     * @param object $request
     * @param object $response
     * @param array $args
     * @return object
     */
    public function update($request, $response, $args)
    {
        $this->requireControlPanelPermission();
        return $this->install($request, $response, $args);
    }

    /**
     * Enable/Disable extension
     *
     * @param object $request
     * @param object $response
     * @param array $args
     * @return object
     */
    public function changeStatus($request, $response, $args)
    {
        $this->requireControlPanelPermission();
        $extension_manager = new ExtensionsManager();
        $form = Form::create($args);

        $form->addRule('status',Form::Rule()->checkList([0,1,'toggle']));
        $form->addRule('name',Form::Rule()->extensionPath($args['name']));

        if ($form->validate() == true) {
            try {
                $name = $form->get('name');  
                $result['status'] = $form->get('status');                      
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
            $this->setApiErrors($form->getErrors());
        }
        return $this->getApiResponse();
    }

    /**
     * Uninstall extension
     *
     * @param object $request
     * @param object $response
     * @param array $args
     * @return object
     */
    public function unInstall($request, $response, $args)
    {
        $this->requireControlPanelPermission();
        $form = Form::create($args);

        $form->addRule('name',Form::Rule()->exists('Extensions','name'));
   
        if ($form->validate() == true) {
            $extension_manager = new ExtensionsManager();
            $result = $extension_manager->unInstall($args['name']);
            if ($result == false) {
                $this->setApiError(Arikaim::getError("SYSTEM_ERROR",['extension_name' => "$extension_name"]));
            }              
        } else {
            $this->setApiError(Arikaim::getError("EXTENSION_NOT_EXISTS"));
        }
        return $this->getApiResponse();   
    }

    /**
     * Install extension
     *
     * @param object $request
     * @param object $response
     * @param array $args
     * @return object
     */
    public function install($request, $response, $args)    
    {       
        $this->requireControlPanelPermission();
        $form = Form::create($args);

        $form->addRule('name',Form::Rule()->extensionPath($args['name']));
    
        if ($form->validate() == true) {
            $extension_manager = new ExtensionsManager();
            $result = $extension_manager->install($args['name']);
            if ($result == false) {
                $this->setApiError(Arikaim::getError("EXTENSION_INSTALL_ERROR",['extension_name' => "$extension_name"]));
            }
        } else {
            $this->setApiError(Arikaim::getError("EXTENSION_NOT_EXISTS"));
        }        
        return $this->getApiResponse();   
    }
}
