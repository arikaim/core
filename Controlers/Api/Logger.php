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
class Logger extends ApiControler
{
    /**
     * Remove system logs
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
     */
    public function clear($request, $response, $data)
    {
        $this->requireControlPanelPermission();

        $result = Arikaim::logger()->deleteSystemLogs();
        if ($result == false) {
            $this->setApiErrors(Arikaim::errors()->getError("DELETE_FILE_ERROR"));
        }
        return $this->getApiResponse();
    }
}
