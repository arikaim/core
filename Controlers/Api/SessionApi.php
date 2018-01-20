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
use Arikaim\Core\Install\Install;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Form\Form;

class SessionApi extends ApiControler
{
    public function setValue($request, $response, $args) 
    {           
        $this->form->setFields($request->getParsedBody());
        $this->form->addRule('key',Form::Rule()->text(1),true);  
        if ($this->form->validate() == false) {
            $this->setApiErrors($this->form->getErrors());
        } else {
            $value = $this->form->get('value');
            $key = $this->form->get('key');
            Arikaim::session()->set($key,$value);
        }
        return $this->getApiResponse();
    }

    public function getInfo($request, $response, $args) 
    {           
        $session_info = Arikaim::session()->getParams();   
        $this->setApiResult($session_info);
        return $this->getApiResponse();
    }

    public function restart($request, $response, $args) 
    {           
        Arikaim::session()->recrete();
        $session_info = Arikaim::session()->getParams();       
        $this->setApiResult($session_info);
        return $this->getApiResponse();
    }
}
