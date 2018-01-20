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
use Arikaim\Core\Db\Model;
use Arikaim\Core\Extension\ExtensionsManager;

class TemplatesApi extends ApiControler
{
    public function setCurrent($request, $response, $args)
    {       
        $this->form->addRule('name',Form::Rule()->templatePath($args['name']));
        $this->form->validate($args);
        if ($this->form->isValid() == true) {
            try {
                $name = $this->form->get('name');  
                $result = Arikaim::options()->set('current.template',$name);
                if ($result == false) {
                    $this->setApiError(Arikaim::getError("SYSTEM_ERROR"));
                } else {                
                    $this->setApiResult(['name' => $name]);
                }
            } catch(\Exception $e) {
                $this->setApiError($e->getMessage());
            }
        } else {
            $this->setApiErrors($this->form->getErrors());
        }
        return $this->getApiResponse();
    }
}
