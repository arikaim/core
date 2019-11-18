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
 * Cache controller
*/
class Cache extends ApiController
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
     * Clear cache
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function clearController($request, $response, $data)
    {
        $this->requireControlPanelPermission();
        
        $result = Arikaim::cache()->clear();
        $this->setResponse($result,'cache.clear','errors.cache.clear');

    }

    /**
     * Enable cache
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function enableController($request, $response, $data)
    {
        $this->requireControlPanelPermission();
        
        $debug = $data->get('debug',false);
       
        Arikaim::config()->setBooleanValue('settings/cache',true);
        $result = Arikaim::config()->save();

        Arikaim::cache()->clear();

        $this->setResponse($result,'cache.enable','errors.cache.enable');
    }

    /**
     * Disable cache
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function disableController($request, $response, $data)
    {
        $this->requireControlPanelPermission();
        
        Arikaim::cache()->clear();

        $debug = $data->get('debug',false);
       
        Arikaim::config()->setBooleanValue('settings/cache',false);
        $result = Arikaim::config()->save();
     
        $this->setResponse($result,'cache.disable','errors.cache.disable');
    }
}
