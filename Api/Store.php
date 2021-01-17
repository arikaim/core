<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Api;

use Arikaim\Core\Controllers\ControlPanelApiController;
use Arikaim\Core\App\ArikaimStore;

/**
 * Arikaim store controller
*/
class Store extends ControlPanelApiController
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
     * Save order id
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function saveOrderController($request, $response, $data)
    {
        $uuid = $data->get('uuid');
        $orderId = $data->get('order_id');
      
        $store = new ArikaimStore();

        $store->getConfig()->setValue('product/uuid',$uuid);
        $store->getConfig()->setValue('product/id',$orderId);

        // save and reload config file
        $result = $store->getConfig()->save();

        $this->setResponse($result,'store.order','errors.store.order');     
    }
}
