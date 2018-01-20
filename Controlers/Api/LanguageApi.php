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

use Arikaim\Core\Db\Model;
use Arikaim\Core\Form\Form;
use Arikaim\Core\Controlers\ApiControler;

class LanguageApi  extends ApiControler
{
    public function add($request, $response, $args) 
    {       
        $update = true;    
        $this->form->setFields($request->getParsedBody());       
        $uuid = $this->form->get('uuid');
        if (empty($uuid) == true) {
            // add record rules
            $this->form->addRule('code',Form::Rule()->unique('Language','code'));
            $this->form->addRule('code_3',Form::Rule()->unique('Language','code_3'));  
            $update = false;    
        }
        $this->form->addRule('title',Form::Rule()->text(2));
        $this->form->addRule('native_title',Form::Rule()->text(2),false);
        $this->form->addRule('code',Form::Rule()->text(2,2));
        $this->form->addRule('code_3',Form::Rule()->text(3,3));
        $this->form->addRule('language_code',Form::Rule()->text(2,2));

        if ($this->form->validate() == true) {
            try {               
                $language = Model::Language();
                if ($update == true) {       
                    // update record
                    $language = $language->where('uuid',$uuid)->first();           
                    $result = $language->update($this->form->toArray());                                      
                } else {
                    // add record                          
                    $language->fill($this->form->toArray());        
                    $result = $language->save();
                }
                if ($result == false) {
                    $this->setApiError("ERROR_SAVE_DATA");
                }
            } catch(\Exception $e) {
                $this->setApiError($e->getMessage());
            }
        } else {
            $this->setApiErrors($this->form->getErrors());
        }
        return $this->getApiResponse();   
    }

    public function remove($request, $response, $args)
    {
        $this->form->addRule('uuid',Form::Rule()->exists('Language','uuid'));
        if ($this->form->validate($args) == true) {
            try {
                $language = Model::Language()->where('uuid','=',$args['uuid'])->first();
                $language->delete();
            } catch(\Exception $e) {
                $this->setApiError($e->getMessage());
            }
        } else {
            $this->setApiErrors($this->form->getErrors());
        }       
        return $this->getApiResponse();     
    }
    
    public function setStatus($request, $response, $args)
    {         
        $this->form->addRule('uuid',Form::Rule()->exists('Language','uuid'));
        $this->form->addRule('status',Form::Rule()->checkList([0,1,'toggle']));
        if ($this->form->validate($args) == true) {
            try {
                $status = $this->form->get('status');               
                if ($status == 'toggle') {
                    $result['status'] = Model::Language()->toggleValue($args['uuid'],'status');
                } else {  
                    $language = Model::Language()->where('uuid','=',$args['uuid'])->first();                  
                    $language->status = $status;
                    $result = $language->update(); 
                    $result['status'] = $status;
                }               
                $this->setApiResult($result);
            } catch(\Exception $e) {
                $this->setApiError($e->getMessage());
            }
        } else {
            $this->setApiErrors($this->form->getErrors());
        }
        return $this->getApiResponse();   
    }

    public function setDefault($request, $response, $args)
    {
        $this->form->addRule('uuid',Form::Rule()->exists('Language','uuid'));
        if ($this->form->validate($args) == true) {
            try {
                // set other default to 0
                $language = Model::Language()->where('uuid','<>',$args['uuid']);
                $language->update(['default' => 0]);
                // set selected default to 1
                $language = Model::Language()->where('uuid','=',$args['uuid'])->first();
                $language->default = 1;
                $language->update();       
            } catch(\Exception $e) {
                $this->setApiError($e->getMessage());
            }
        } else {
            $this->setApiErrors($this->form->getErrors());
        }
        return $this->getApiResponse();   
    }

    public function changeOrder($request, $response, $args)
    {
        $this->form->addRule('uuid',Form::Rule()->exists('Language','uuid'));
        $this->form->addRule('after_uuid',Form::Rule()->exists('Language','uuid'));
        if ($this->form->validate($args) == true) {
            try {
                $language = Model::Language()->where('uuid','=',$args['uuid'])->first();              
                $result = $language->movePositionAfter($args['after_uuid']);                 
            } catch(\Exception $e) {
                $this->setApiError($e->getMessage());
            }
        } else {
            $this->setApiErrors($this->form->getErrors());
        }
        return $this->getApiResponse();   
    }

    public function changeLanguage($request, $response, $args)
    {
        $this->form->addRule('language_code',Form::Rule()->exists('Language','code'));
        if ($this->form->validate($args) == true) {
            Arikaim::setLanguage($this->form->get("language_code"));
        } else {
            $this->setApiErrors($this->form->getErrors());
        }
        return $this->getApiResponse();
    }
}
