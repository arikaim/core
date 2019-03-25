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
use Arikaim\Core\Arikaim;

/**
 * Control Panel controler
*/
class Queue extends ApiControler
{
    public function deleteJobs($request, $response, $data)
    {
        $this->requireControlPanelPermission();
        
        Arikaim::jobs()->getQueueService()->removeAllJobs();
        return $this->getApiResponse();
    }
    
    public function updateJobs($request, $response, $data)
    {
        $this->requireControlPanelPermission();
        
        Arikaim::jobs()->update();
        return $this->getApiResponse();
    } 
}
