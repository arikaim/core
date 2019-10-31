<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Controllers\Api;

use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\Arikaim;

/**
 * Settings controller
*/
class Settings extends ApiController
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
     * Save debug setting
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function setDebug($request, $response, $data)
    {
        $this->requireControlPanelPermission();
        
        $debug = $data->get('debug',false);
       
        Arikaim::config()->setBooleanValue('settings/debug',$debug);
        // save and reload config file
        $result = Arikaim::config()->save();
        $this->setResponse($result,'settings.save','errors.settings.save');

        return $this->getResponse();
    }
}
