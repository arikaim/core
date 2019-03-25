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
use Arikaim\Core\System\Update as SystemUpdate;

/**
 * Update controler
*/
class Update extends ApiControler
{
    /**
     * Update Arikaim
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
     */
    public function update($request, $response, $data) 
    {           
        $this->requireControlPanelPermission();
        $update = new SystemUpdate();
        $result = $update->update();
        if ($result == false) {
            $this->setApiError('Error update arikaim core.');
        }
        return $this->getApiResponse();
    }
    
    public function checkVersion($request, $response, $data)
    {
        return $this->getApiResponse();
    }
}
