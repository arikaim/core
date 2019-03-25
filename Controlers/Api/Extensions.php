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
use Arikaim\Core\Packages\Extension\ExtensionsManager;
use Arikaim\Core\Arikaim;

/**
 * Extensions Api controler
*/
class Extensions extends ApiControler
{
    
    /**
     * Update extension
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
     */
    public function update($request, $response, $data)
    {
        $this->requireControlPanelPermission();

        return $this->install($request, $response, $data);
    }

    /**
     * Enable/Disable extension
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
     */
    public function changeStatus($request, $response, $data)
    {
        $this->requireControlPanelPermission();
        $manager = new ExtensionsManager();
     
        $valid = $data
            ->addRule('status',$data->rule()->checkList([0,1,'toggle']))
            ->addRule('name',$data->rule()->extensionPath($data['name']))
            ->validate();

        if ($valid == true) {
            try {
                $name = $data->get('name');  
                $result['status'] = $data->get('status');                      
                if ($result['status'] == 'toggle') {  
                    $status = Model::Extensions()->getStatus($name);                    
                    $result['status'] = ($status == 1) ? 0 : 1;
                } 
                if ($result['status'] == 1) {
                    $manager->enablePackage($name);
                } else {
                    $manager->disablePackage($name);
                }
                $this->setApiResult($result);
            } catch(\Exception $e) {
                $this->setApiError($e->getMessage());
            }
        } else {
            $this->setApiErrors($data->getErrors());
        }
        return $this->getApiResponse();
    }

    /**
     * Uninstall extension
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
     */
    public function unInstall($request, $response, $data)
    {
        $this->requireControlPanelPermission();

        $data->addRule('name',$data->rule()->exists('Extensions','name'));
   
        if ($data->validate() == true) {
            $manager = new ExtensionsManager();
            $result = $manager->unInstallPackage($data['name']);
            if ($result == false) {
                $this->setApiError(Arikaim::getError("SYSTEM_ERROR",['extension_name' => $data['name']]));
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
     * @param Validator $data
     * @return object
     */
    public function install($request, $response, $data)    
    {       
        $this->requireControlPanelPermission();

        $data->addRule('name',$data->rule()->extensionPath($data['name']));
    
        if ($data->validate() == true) {
            $manager = new ExtensionsManager();
            $result = $manager->installPackage($data['name']);
            if ($result == false) {
                $this->setApiError(Arikaim::getError("EXTENSION_INSTALL_ERROR",['extension_name' => $data['name']]));
            }
        } else {
            $this->setApiError(Arikaim::getError("EXTENSION_NOT_EXISTS"));
        }        
        return $this->getApiResponse();   
    }
}
