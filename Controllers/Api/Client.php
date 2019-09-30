<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Controllers\Api;

use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\Db\Model;

/**
 * Api client controller   TODO
*/
class ApiClient extends ApiController
{
    /**
     * Create auth token
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function createTokenController($request, $response, $data) 
    {               
        $this->onDataValid(function($data) { 
            $this->loadMessages('system:admin.messages');      
            // TODO
            $user = Model::Users()->getApiUser($data->get('api_key'),$data->get('api_secret'));
           // if ($user == false) {
              //  $this->setError("Not valid api key or api secret!");
           // } else {
                $token = Arikaim::access()->createToken($user->id);            
                $this->setResult($token);     
          //  }   
            $result = '';
            $this->setResponse($result,function() use($token) {
                $this
                    ->message('token.create')
                    ->field('token',$token);
            },'errors.token.create');
        });
        $data
            ->addRule("text:min=5","api_key")
            ->addRule("text:min=5","api_secret")
            ->validate();       
    }
}
