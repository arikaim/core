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
use Arikaim\Core\Packages\PackageManagerFactory;

/**
 * Packages controller
*/
class Packages extends ApiController
{
    /**
     * Init controller
     *
     * @return void
     */
    public function init()
    {
        $this->loadMessages('system:admin.messages');
    }

    /**
     * Dowload and install package from repository
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function installController($request, $response, $data)
    {
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) {            
            $type = $data->get('type',null);
            $name = $data->get('package',null);

            $packageManager = PackageManagerFactory::create($type);
            $package = $packageManager->createPackage($name);
            $result = (is_object($package) == true) ? $package->getRepository()->install() : false;

            $this->setResponse($result,function() use($name,$type) {                  
                $this
                    ->message($type . '.install')
                    ->field('type',$type)   
                    ->field('name',$name);                  
            },'errors.' . $type . '.install');
        });
        $data->validate();       
    }
}
