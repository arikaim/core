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

/**
 * System options controler
*/
class OptionsApi extends ApiControler
{
    /**
     * Save option
     *
     * @param object $request
     * @param object $response
     * @param object $args
     * @return object
    */
    public function save($request, $response, $args) 
    {                
        // access from contorl panel only 
        $this->requireControlPanelPermission();
        $form = Form::create($request->getParsedBody());    

        $form->addRule('key',Form::Rule()->text(2)); 

        if ($form->validate() == true) {
            $key = $form->get('key');
            $value = $form->get('value');
            Arikaim::options()->set($key,$value);
            $this->setApiResult($form->getFields());
        } else {
            $this->setApiError($form->getErrors());
        }
        return $this->getApiResponse();
    }

    /**
     * Get option
     *
     * @param object $request
     * @param object $response
     * @param object $args
     * @return object
    */
    public function get($request, $response, $args) 
    {                   
        $form = Form::create($args);   
        $form->addRule('key',Form::Rule()->exists('Settings','key'));    

        if ($form->validate() == true) {
            $value = Arikaim::options()->get($args['key']);
            $this->setApiResult($value);
        } else {    
            $this->setApiError($form->getErrors());
        }
        return $this->getApiResponse();
    }

    /**
     * Save options
     *
     * @param object $request
     * @param object $response
     * @param object $args
     * @return object
    */
    public function saveOptions($request, $response, $args) 
    {    
        // access from contorl panel only 
        $this->requireControlPanelPermission();
        $form = Form::create($request->getParsedBody());    

        if ($form->validate() == true) {
            $fields = $form->toArray();
            foreach ($fields as $key => $value) {
                Arikaim::options()->set($key,$value);
            }
        } else {
            $this->setApiError($form->getErrors());
        }
        return $this->getApiResponse();
    }
}
