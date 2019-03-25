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
     * @param Validator $data
     * @return object
     */
    public function createToken($request, $response, $data) 
    {               
        $valid = $data
            ->addRule('api_key',$data->rule()->text(5))
            ->addRule('api_secret',$data->rule()->text(5))
            ->validate();
      
        $user = false;
        if ($valid == true) {
            $user = Model::Users()->getApiUser($data->get('api_key'),$data->get('api_secret'));
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
     * @param Validator $data
     * @return object
     */
    public function verifyRequest($request, $response, $data) 
    {
        $valid = $data
            ->addRule('method',$data->rule()->text(3))
            ->addRule('path',$data->rule()->text(3))
            ->validate();
    
        if ($valid == false) {
            $this->setApiError("Not valid api request!");
            return $this->getApiResponse();
        }

        $model = Model::Routes();
        $condition = Model::createCondition('method','=',$data->get('method'));
        $condition->addCondition('path','=',$data->get('path'));
        $condition->addCondition('type','=',2);
        $condition->addCondition('status','=',1);
        $route = $model->findRoute($condition);

        if ($route == false) {
            $this->setApiError("Not valid api request!");
        }
        return $this->getApiResponse();
    }
}
