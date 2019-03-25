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
class Settings extends ApiControler
{
    public function save($request, $response, $data)
    {
        $this->requireControlPanelPermission();
         
        $cors = $data->get('cors',false);
        $debug = $data->get('debug',false);
       
        Arikaim::config()->setBooleanValue('settings/cors',$cors);
        Arikaim::config()->setBooleanValue('settings/debug',$debug);
        // save and reload config file
        Arikaim::config()->saveConfigFile();
       
        return $this->getApiResponse();
    }
}
