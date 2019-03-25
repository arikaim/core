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

/**
 * System options controler
*/
class Options extends ApiControler
{
    /**
     * Save option
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function save($request, $response, $data) 
    {                
        // access from contorl panel only 
        $this->requireControlPanelPermission();

        $valid = $data
            ->addRule('key',$data->rule()->text(2))
            ->validate();

        if ($valid == true) {         
            Arikaim::options()->set($data['key'],$data['value']);
            $this->setApiResult($data->toArray());
        } else {
            $this->setApiError($data->getErrors());
        }
        return $this->getApiResponse();
    }

    /**
     * Get option
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function get($request, $response, $data) 
    {                   
        $data->addRule('key',$data->rule()->exists('Settings','key'));    

        if ($data->validate() == true) {
            $value = Arikaim::options()->get($data['key']);
            $this->setApiResult($value);
        } else {    
            $this->setApiError($data->getErrors());
        }
        return $this->getApiResponse();
    }

    /**
     * Save options
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function saveOptions($request, $response, $data) 
    {    
        // access from contorl panel only 
        $this->requireControlPanelPermission();

        if ($data->validate() == true) {         
            foreach ($data as $key => $value) {
                Arikaim::options()->set($key,$value);
            }
        } else {
            $this->setApiError($data->getErrors());
        }
        return $this->getApiResponse();
    }
}
