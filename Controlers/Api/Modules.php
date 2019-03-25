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
use Arikaim\Core\Packages\Module\ModulesManager;
use Arikaim\Core\Arikaim;

/**
 * Core api modules controler
*/
class Modules extends ApiControler
{
    public function unInstallModule($request, $response, $data)
    {
        $this->requireControlPanelPermission();
      
        $manager = new ModulesManager();
        $result = $manager->unInstallPackage($data['name']);
    
        return $this->getApiResponse();
    }

    public function installModule($request, $response, $data)
    {
        $this->requireControlPanelPermission();
        
        $manager = new ModulesManager();
        $result = $manager->installPackage($data['name']);
    
        if ($result == false) {
            $this->setApiError("Error install module " . $data['name']);
        }
        return $this->getApiResponse();
    }

    public function enableModule($request, $response, $data)
    {
        $this->requireControlPanelPermission();

        $manager = new ModulesManager();
        $result = $manager->enablePackage($data['name']);

        if ($result == false) {
            $this->setApiError("Error enable module " . $data['name']);
        }
        return $this->getApiResponse();
    }

    public function disableModule($request, $response, $data)
    {
        $this->requireControlPanelPermission();

        $manager = new ModulesManager();
        $result = $manager->disablePackage($data['name']);
        
        if ($result == false) {
            $this->setApiError("Error enable module " . $data['name']);
        }
        return $this->getApiResponse();
    }
}
