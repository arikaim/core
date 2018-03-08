<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Controlers\Pages;

use Arikaim\Core\Db\Model;
use Arikaim\Core\System\Install;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Controlers\Controler;
use Arikaim\Core\System\Config;

class PageLoader extends Controler 
{
    public function __construct() 
    {
        parent::__construct();
    }

    public function loadTemplatePage($request, $response, $args)
    {
        $route = $request->getAttribute('route');  
        if (is_object($route) == false) {
            Arikaim::page()->load("system-error",$args);
            return $response;
        }    
        $path = $route->getPattern();
        $model = Model::Routes()->getRoute('GET',$path);
        if ($model == false) {
            return $this->pageNotFound($request,$response,$args);
        }
        return $this->loadPage($request,$response,['page' => $model->template_page]);
    }

    public function loadPage($request, $response, $args)
    {
        if (isset($args['page']) == true) {
            $page_name = $args['page'];           
        } else {
            return $this->pageNotFound($request,$response,$args);
        }

        if (Arikaim::page()->has($page_name) == true) {
            Arikaim::page()->load($page_name,$args);
            return $response;       
        } 
        return $this->pageNotFound($request,$response,$args);
    }

    public function loadControlPanel($request, $response, $args) 
    {   
        if (Install::isInstalled() == false) { 
            return $this->loadPage($request,$response,['page' => 'system:install']);
        }
        $user = Model::Users()->getLogedUser();    
        $loged_in = false;     
      
        if ($user != false) {
            $loged_in = Arikaim::access()->hasControlPanelAccess($user->uuid);
        }         
        return $this->loadPage($request,$response,['page' => 'system:admin','loged_in' => $loged_in]);    
    }

    public function pageNotFound($request, $response, $args = []) 
    {                  
        if (Install::isInstalled() == false) { 
            return $this->loadPage($request,$response,['page' => 'system:install']);
        }
        Arikaim::page()->load("page-not-found",$args);  
        return $response->withStatus(404);
    }

    public function loadInstallPage($request, $response, $args = [])
    {
        if (Install::isInstalled() == false) {                             
            return $this->loadPage($request,$response,['page' => 'system:install']);
        }
        Arikaim::errors()->addError('INSTALLED_ERROR');
        return $this->loadPage($request,$response,['page' => 'system-error']);
    }
}
