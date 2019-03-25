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
use Arikaim\Core\System\Install as SystemInstall;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;

/**
 * Update controler
*/
class Install extends ApiControler
{
    /**
     * Install Arikaim
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
     */
    public function install($request, $response, $data) 
    {           
        Model::Users()->logout();
        
        $install = new SystemInstall();
        $valid = $data
            ->addRule('database',$data->rule()->text(2))
            ->addRule('username',$data->rule()->text(2))
            ->addRule('password',$data->rule()->text(2))
            ->validate();
 
        if ($valid == true) { 
            // save config file               
            Arikaim::config()->setValue('db/username',$data->get('username'));
            Arikaim::config()->setValue('db/password',$data->get('password'));
            Arikaim::config()->setValue('db/database',$data->get('database'));
            // save and reload config file
            Arikaim::config()->saveConfigFile();
            
            $result = Arikaim::db()->testConnection(Arikaim::config('db'));
            if ($result == true) {          
                // do install
                $result = $install->install();     
            }          
        } else {
            $this->setApiErrors($data->getErrors());
        }
        return $this->getApiResponse();
    }
}
