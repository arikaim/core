<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Controllers;

use Arikaim\Core\System\Install;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Controllers\Controller;

/**
 * Page loader controller
*/
class PageLoader extends Controller 
{   
    /**
     * Load control panel page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function loadControlPanel($request, $response, $data) 
    {         
        if (Install::isInstalled() == false) { 
            return $this->loadInstallPage($request,$response,$data);
        }   
        $data['page_name'] = 'system:admin';
        return $this->loadPage($request,$response,$data);       
    }

    /**
     * Load install page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function loadInstallPage($request, $response, $data)
    {
        if (Install::isInstalled() == false) { 
            Arikaim::cache()->clear();
            $data['page_name'] = 'system:install';     
            return $this->loadPage($request,$response,$data);                   
        }
        Arikaim::errors()->addError('INSTALLED_ERROR');
        $data['page_name'] = 'system:system-error';     
        return $this->loadPage($request,$response,$data);    
    }
}
