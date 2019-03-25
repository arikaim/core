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
 * Session controler
*/
class Session extends ApiControler
{
    /**
     * Save session value
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function setValue($request, $response, $data) 
    {                 
        $data->addRule('key',$data->rule()->text(1),true);

        if ($data->validate() == false) {
            $this->setApiErrors($data->getErrors());
        } else {           
            Arikaim::session()->set($data['key'],$data['value']);
        }
        return $this->getApiResponse();
    }

    /**
     * Return session info
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function getInfo($request, $response, $data) 
    {           
        $session_info = Arikaim::session()->getParams();   
        $session_info['recreate'] = Arikaim::options()->get('session.recreation.interval');
        $this->setApiResult($session_info);
        return $this->getApiResponse();
    }

    /**
     * Recreate session
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function restart($request, $response, $data) 
    {           
        Arikaim::session()->recrete();
        $session_info = Arikaim::session()->getParams();  
        $session_info['recreate'] = Arikaim::options()->get('session.recreation.interval');     
        $this->setApiResult($session_info);
        return $this->getApiResponse();
    }
}
