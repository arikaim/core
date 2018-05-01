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
use Arikaim\Core\View\Template;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Form\Form;
use Arikaim\Core\Controlers\ApiControler;

/**
 * Languages Api controler
*/
class LanguageApi extends ApiControler
{
    /**
     * Add or edit language
     *
     * @param object $request
     * @param object $response
     * @param object $args
     * @return object
     */
    public function add($request, $response, $args) 
    {       
        // access from contorl panel only 
        $this->requireControlPanelPermission();

        $language = Model::Language();
        $update = false;    
        $this->form->setFields($request->getParsedBody());       
        $uuid = $this->form->get('uuid');
        if (empty($uuid) == false) {
            // edit record rules
            $update = true;
            $language = $language->where('uuid',$uuid)->first();
        } 

        $this->form->addRule('code',Form::Rule()->unique('Language','code',null,$language->code)); 
        $this->form->addRule('code_3',Form::Rule()->unique('Language','code_3',null,$language->code_3)); 
        $this->form->addRule('title',Form::Rule()->text(2));
        $this->form->addRule('native_title',Form::Rule()->text(2),false);
        $this->form->addRule('code',Form::Rule()->text(2,2));
        $this->form->addRule('code_3',Form::Rule()->text(3,3));
        $this->form->addRule('language_code',Form::Rule()->text(2,2));

        if ($this->form->validate() == true) {
            try {               
                if ($update == true) {       
                    // update record
                    $result = $language->update($this->form->toArray());                                      
                } else {
                    // add record 
                    if ($language->has($this->form->get('code')) == true) {
                        // language exists
                        $error = Arikaim::getError("LANGUAGE_EXISTS",['code' => $this->form->get('code')]);
                        $this->form->setError('code',$error);
                        $this->setApiErrors($this->form->getErrors());
                        return $this->getApiResponse(); 
                    }
                    $language->fill($this->form->toArray());        
                    $result = $language->save();
                }
                if ($result == false) {
                    $this->setApiError(Arikaim::errors()->getError("ERROR_SAVE_DATA"));
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
        // access from contorl panel only 
        $this->requireControlPanelPermission();

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
        // access from contorl panel only 
        $this->requireControlPanelPermission();

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
        // access from contorl panel only 
        $this->requireControlPanelPermission();

        $this->form->addRule('uuid',Form::Rule()->exists('Language','uuid'));
        if ($this->form->validate($args) == true) {
            try {
                // set other default to 0
                $language = Model::Language()->where('uuid','<>',$args['uuid']);
                $language->update(['default' => 0]);
                // set selected default to 1
                $language = Model::Language()->where('uuid','=',$args['uuid'])->first();
                $language->default = 1;
                $language->status = 1;
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
        // access from contorl panel only 
        $this->requireControlPanelPermission();
        
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
            Template::setLanguage($this->form->get("language_code"));
        } else {
            $this->setApiErrors($this->form->getErrors());
        }
        return $this->getApiResponse();
    }
}
