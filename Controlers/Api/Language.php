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
use Arikaim\Core\Controlers\ApiControler;

/**
 * Languages Api controler
*/
class Language extends ApiControler
{
    /**
     * Add or edit language
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
     */
    public function add($request, $response, $data) 
    {       
        // access from contorl panel only 
        $this->requireControlPanelPermission();
        
        $language = Model::Language();
        $uuid = $data->get('uuid');
        if (empty($uuid) == false) {
            $update = true;
            $language = $language->where('uuid','=',$uuid)->first();
        } else {
            $update = false;
        }
        $valid = $data
            ->addRule('code',$data->rule()->unique('Language','code',null,$language->code))
            ->addRule('code_3',$data->rule()->unique('Language','code_3',null,$language->code_3))        
            ->addRule('title',$data->rule()->text(2))
            ->addRule('native_title',$data->rule()->text(2),false)
            ->addRule('code',$data->rule()->text(2,2))
            ->addRule('code_3',$data->rule()->text(3,3))
            ->addRule('language_code',$data->rule()->text(2,2))
            ->validate();

        if ($valid == true) {
            try {               
                if ($update == false) {       
                    // add record 
                    if ($language->has($data->get('code')) == true) {
                        // language exists
                        $error = Arikaim::getError("LANGUAGE_EXISTS",['code' => $data->get('code')]);
                        $data->setError('code',$error);
                        $this->setApiErrors($data->getErrors());
                        return $this->getApiResponse(); 
                    }
                    $result = $language->add($data->toArray());                      
                } else {
                    $result = $language->update($data->toArray());    
                }
            } catch(\Exception $e) {
                $this->setApiError($e->getMessage());
            }
        } else {
            $this->setApiErrors($data->getErrors());
        }
        return $this->getApiResponse();   
    }

    /**
     * Remove language
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function remove($request, $response, $data)
    {
        // access from contorl panel only 
        $this->requireControlPanelPermission();
    
        $data->addRule('uuid',$data->rule()->exists('Language','uuid'));
        if ($data->validate() == true) {
            try {
                $language = Model::Language()->where('uuid','=',$data['uuid'])->first();
                $language->delete();
            } catch(\Exception $e) {
                $this->setApiError($e->getMessage());
            }
        } else {
            $this->setApiErrors($data->getErrors());
        }       
        return $this->getApiResponse();     
    }
    
    /**
     * Enable/Disable language
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function setStatus($request, $response, $data)
    {         
        // access from contorl panel only 
        $this->requireControlPanelPermission();
      
        $valid = $data->addRule('uuid',$data->rule()->exists('Language','uuid'))
            ->addRule('status',$data->rule()->checkList([0,1,'toggle']))
            ->validate();

        if ($valid == true) {
            try {
                $status = $data->get('status');               
                if ($status == 'toggle') {
                    $result['status'] = Model::Language()->toggleValue($data['uuid'],'status');
                } else {  
                    $language = Model::Language()->where('uuid','=',$data['uuid'])->first();                  
                    $language->status = $status;
                    $result = $language->update(); 
                    $result['status'] = $status;
                }               
                $this->setApiResult($result);
            } catch(\Exception $e) {
                $this->setApiError($e->getMessage());
            }
        } else {
            $this->setApiErrors($data->getErrors());
        }
        return $this->getApiResponse();   
    }

    /**
     * Set default language
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function setDefault($request, $response, $data)
    {
        // access from contorl panel only 
        $this->requireControlPanelPermission();
    
        $valid = $data
            ->addRule('uuid',$data->rule()->exists('Language','uuid'))
            ->validate();

        if ($valid == true) {
            try {
                // set other default to 0
                $language = Model::Language()->where('uuid','<>',$data['uuid']);
                $language->update(['default' => 0]);
                // set selected default to 1
                $language = Model::Language()->where('uuid','=',$data['uuid'])->first();
                $language->default = 1;
                $language->status = 1;
                $language->update();       
            } catch(\Exception $e) {
                $this->setApiError($e->getMessage());
            }
        } else {
            $this->setApiErrors($data->getErrors());
        }
        return $this->getApiResponse();   
    }

    /**
     * Set language order
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function changeOrder($request, $response, $data)
    {
        // access from contorl panel only 
        $this->requireControlPanelPermission();
    
        $valid = $data
            ->addRule('uuid',$data->rule()->exists('Language','uuid'))
            ->addRule('after_uuid',$data->rule()->exists('Language','uuid'))
            ->validate();

        if ($valid == true) {
            try {
                $language = Model::Language()->where('uuid','=',$data['uuid'])->first();              
                $result = $language->movePosition($language,$data['after_uuid']);                 
            } catch(\Exception $e) {
                $this->setApiError($e->getMessage());
            }
        } else {
            $this->setApiErrors($data->getErrors());
        }
        return $this->getApiResponse();   
    }

    /**
     * Change language order
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function changeLanguage($request, $response, $data)
    {
        $this->requireControlPanelPermission();
    
        $data->addRule('language_code',$data->rule()->exists('Language','code'));

        if ($data->validate() == true) {
            Template::setLanguage($data->get("language_code"));
        } else {
            $this->setApiErrors($data->getErrors());
        }
        return $this->getApiResponse();
    }
}
