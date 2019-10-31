<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Controllers\Api;

use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\Arikaim;

/**
 * Session controller
*/
class Session extends ApiController
{
    /**
     * Get session info
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function getInfo($request, $response, $data) 
    {           
        $sessionInfo = Arikaim::session()->getParams();   
        $sessionInfo['recreate'] = Arikaim::options()->get('session.recreation.interval');
        
        return $this->setResult($sessionInfo)->getResponse();       
    }

    /**
     * Recreate session
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function recreate($request, $response, $data) 
    {             
        $lifetime = $data->get('$lifetime',null);
        Arikaim::session()->recrete($lifetime);

        $sessionInfo = Arikaim::session()->getParams();  
        $sessionInfo['recreate'] = Arikaim::options()->get('session.recreation.interval');     
        
        return $this->setResult($sessionInfo)->getResponse();       
    }

     /**
     * Restart session
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function restart($request, $response, $data) 
    { 
        $this->requireControlPanelPermission();
        
        $lifetime = $data->get('$lifetime',null);
        Arikaim::session()->restart($lifetime);

        $sessionInfo = Arikaim::session()->getParams();  
        return $this->setResult($sessionInfo)->getResponse();       
    }
}
