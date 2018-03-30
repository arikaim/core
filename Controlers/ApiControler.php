<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Controlers;

use Arikaim\Core\Form\Form;
use Arikaim\Core\Api\ApiResponse;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Controlers\Controler;

class ApiControler extends Controler
{    
    protected $form;
    protected $api_response;

    public function __construct() 
    {
        parent::__construct();
        $this->type = Controler::API;
        $this->form = new Form();
        $this->api_response = new ApiResponse(Arikaim::response(),Arikaim::settings('debug'),Arikaim::settings('debugTrace'));     
    }

    public function getApiResponse() 
    {
        $this->api_response->addErrors(Arikaim::errors()->getErrors());
        return $this->api_response->getResponse();   
    } 

    public function setApiError($error_message) 
    {
        return $this->api_response->setError($error_message);   
    }
    
    public function setApiErrors(array $errors)
    {
        return $this->api_response->setErrors($errors);
    }

    public function setApiResult($result_code)
    {     
        $this->api_response->setResult($result_code);
    }
}
