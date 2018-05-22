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
use Arikaim\Core\Form\Form;

/**
 * Session controler
*/
class SessionApi extends ApiControler
{
    /**
     * Save session value
     *
     * @param object $request
     * @param object $response
     * @param object $args
     * @return object
    */
    public function setValue($request, $response, $args) 
    {           
        $form = Form::create($request->getParsedBody());    
        $form->addRule('key',Form::Rule()->text(1),true);

        if ($form->validate() == false) {
            $this->setApiErrors($form->getErrors());
        } else {
            $value = $form->get('value');
            $key = $form->get('key');
            Arikaim::session()->set($key,$value);
        }
        return $this->getApiResponse();
    }

    /**
     * Return session info
     *
     * @param object $request
     * @param object $response
     * @param object $args
     * @return object
    */
    public function getInfo($request, $response, $args) 
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
     * @param object $args
     * @return object
    */
    public function restart($request, $response, $args) 
    {           
        Arikaim::session()->recrete();
        $session_info = Arikaim::session()->getParams();  
        $session_info['recreate'] = Arikaim::options()->get('session.recreation.interval');     
        $this->setApiResult($session_info);
        return $this->getApiResponse();
    }
}
