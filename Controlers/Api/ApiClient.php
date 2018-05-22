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

/**
 * Api client controler
*/
class ApiClient extends ApiControler
{
    /**
     * Create auth token
     *
     * @param object $request
     * @param object $response
     * @param array $args
     * @return object
     */
    public function createToken($request, $response, $args) 
    {           
        $form = Form::create($request->getParsedBody());
        $form->addRule('api_key',Form::Rule()->text(5));
        $form->addRule('api_secret',Form::Rule()->text(5));

        if ($form->validate() == true) {
            $user = Model::Users()->getApiUser($form->get('api_key'),$form->get('api_secret'));
        }

        if ($user == false) {
            $this->setApiError("Not valid api key or api secret!");
        } else {
            $token = Arikaim::access()->createToken($user->id,$user->uuid);            
            $this->setApiResult($token);     
        }       
        return $this->getApiResponse();
    }

    /**
     * Verify api request route
     *
     * @param object $request
     * @param object $response
     * @param array $args
     * @return object
     */
    public function verifyRequest($request, $response, $args) 
    {
        $form = Form::create($request->getParsedBody());
        $form->addRule('method',Form::Rule()->text(3));
        $form->addRule('path',Form::Rule()->text(3));

        if ($form->validate() == false) {
            $this->setApiError("Not valid api request!");
            return $this->getApiResponse();
        }

        $model = Model::Routes();
        $condition = Model::createCondition('method','=',$form->get('method'));
        $condition->addCondition('path','=',$form->get('path'));
        $condition->addCondition('type','=',2);
        $condition->addCondition('status','=',1);
        $route = $model->findRoute($condition);

        if ($route == false) {
            $this->setApiError("Not valid api request!");
        }
        return $this->getApiResponse();
    }
}
