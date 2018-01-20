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

use Arikaim\Core\Arikaim;
use Arikaim\Core\Controlers\ApiControler;
use Arikaim\Core\Form\Form;

class OptionsApi extends ApiControler
{
    public function save($request, $response, $args) 
    {                
        $this->form->addRule('key',Form::Rule()->text(2));       
        if ($this->form->validate($request->getParsedBody()) == true) {
            $key = $this->form->get('key');
            $value = $this->form->get('value');
            Arikaim::options()->set($key,$value);
            $this->setApiResult($this->form->getFields());
        } else {
            $this->setApiError($this->form->getErrors());
        }
        return $this->getApiResponse();
    }

    public function get($request, $response, $args) 
    {                   
        $this->form->addRule('key',Form::Rule()->exists('Settings','key'));      
        if ($this->form->validate($args) == true) {
            $value = Arikaim::options()->get($args['key']);
            $this->setApiResult($value);
        } else {    
            $this->setApiError($this->form->getErrors());
        }
        return $this->getApiResponse();
    }

    public function saveOptions($request, $response, $args) 
    {    
        if ($this->form->validate($request->getParsedBody()) == true) {
            $fields = $this->form->toArray();
            foreach ($fields as $key => $value) {
                Arikaim::options()->set($key,$value);
            }
        } else {
            $this->setApiError($this->form->getErrors());
        }
        return $this->getApiResponse();
    }
}
