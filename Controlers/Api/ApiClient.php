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
use Arikaim\Core\Form\Form;
use Arikaim\Core\Db\Model;

class ApiClient extends ApiControler
{
    public function createToken($request, $response, $args) 
    {           
        $this->form->setFields($request->getParsedBody());  
        $this->form->addRule('api_key',Form::Rule()->text(5));
        $this->form->addRule('api_secret',Form::Rule()->text(5));

        if ($this->form->validate() == true) {
            $user = Model::Users()->getApiUser($this->form->get('api_key'),$this->form->get('api_secret'));
        }

        if ($user == false) {
            $this->setApiError("Not valid api key or api secret!");
        } else {
            $token = Arikaim::access()->createToken($user->id,$user->uuid);            
            $this->setApiResult($token);     
        }       
        return $this->getApiResponse();
    }

    public function verifyRequest($request, $response, $args) 
    {
        $this->form->setFields($request->getParsedBody());  
        $this->form->addRule('method',Form::Rule()->text(3));
        $this->form->addRule('path',Form::Rule()->text(3));

        if ($this->form->validate() == false) {
            $this->setApiError("Not valid api request!");
            return $this->getApiResponse();
        }

        $model = Model::Routes();
        $condition = Model::createCondition('method','=', $this->form->get('method'));
        $condition->addCondition('path','=',$this->form->get('path'));
        $condition->addCondition('type','=',2);
        $condition->addCondition('status','=',1);
        $route = $model->findRoute($condition);

        if ($route == false) {
            $this->setApiError("Not valid api request!");
        }
        return $this->getApiResponse();
    }
}
