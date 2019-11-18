<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Controllers\Api;

use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\Arikaim;

/**
 * Drivers controller
*/
class Drivers extends ApiController
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
     * Save driver config
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function saveConfigController($request, $response, $data)
    {
        $this->onDataValid(function($data) {            
            $driverName = $data->get('name');           
            $data->offsetUnset('name');

            $config = Arikaim::driver()->getConfig($driverName);
            // change config valus
            $config->setPropertyValues($data->toArray());
            Arikaim::driver()->saveConfig($driverName,$config);
            $result = true;

            $this->setResponse($result,'drivers.config','errors.drivers.config');
        });
        $data->validate();       
    }

     /**
     * Read driver config
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function readConfigController($request, $response, $data)
    {
        $this->onDataValid(function($data) {            
            $name = $data->get('name');
            $category = $data->get('category',null);
            $result = Arikaim::driver()->getConfig($name,$category);

            $this->setResponse($result,'drivers.config','errors.drivers.config');
        });
        $data->validate();       
    }


    /**
     * Set driver driver status (enable, disable)
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
            $name = $data->get('name');
            $category = $data->get('category',null);
            $status = $data->get('status');

            if ($status == 0) {
                $result = Arikaim::driver()->disable($name,$category);
                $this->setResponse($result,'drivers.disable','errors.drivers.disable');       
            } else {
                $result = Arikaim::driver()->enable($name,$category);
                $this->setResponse($result,'drivers.enable ','errors.drivers.enable');    
            }
        });
        $data->validate();         
    }
}
