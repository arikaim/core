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
use Arikaim\Core\View\Template;
use Arikaim\Core\Utils\Arrays;

/**
 * Page loader controler
*/
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
        $pattern = $route->getPattern();
        $model = Model::Routes()->getRoute('GET',$pattern);
      
        if ($model == false) {
            return $this->pageNotFound($request,$response,$args);
        }
        $params = ['page' => $model->template_page];
        return $this->loadPage($request,$response,Arrays::merge($params,$args));
    }

    public function loadPage($request, $response, $args)
    {
        if (isset($args['page']) == true) {
            $page_name = $args['page'];           
        } else {
            return $this->pageNotFound($request,$response,$args);
        }
       
        $language = $this->getPageLanguage($args);
        if ($language == false) {
            return $this->pageNotFound($request,$response,$args);
        }

        if (Arikaim::page()->has($page_name) == true) {
            Arikaim::page()->load($page_name,$args,$language);
            return $response;       
        } 
        return $this->pageNotFound($request,$response,$args);
    }

    public function getPageLanguage($args)
    {
        if (isset($args['language']) == true) {
            $language = $args['language'];
            if (Model::Language()->has($language,true) == false) {
                return false;
            }
            Template::setLanguage($language);
        } else {            
            $language = Model::Language()->getDefaultLanguage();
            Template::setLanguage($language);
        }
        return $language;
    }

    public function loadControlPanel($request, $response, $args) 
    {   
        if (Install::isInstalled() == false) { 
            return $this->loadInstallPage($request,$response,$args);
        }
        $user = Model::Users()->getLogedUser();    
        if ($user != false) {
            $loged_in = Arikaim::access()->hasControlPanelAccess($user->uuid);
        } else {
            $loged_in = false;
        }        
        $params = ['page' => 'system:admin','loged_in' => $loged_in];
        return $this->loadPage($request,$response,Arrays::merge($params,$args));    
    }

    public function loadChangePassword($request, $response, $args)
    {
        if (isset($args['code']) == true) {
            $code = $args['code'];
            $params = ['page' => 'system:admin/change-password'];
            return $this->loadPage($request,$response,Arrays::merge($params,$args));
        } 
        Arikaim::page()->load("system-error",$args);
        return $response;
    }

    public function pageNotFound($request, $response, $args = []) 
    {                  
        if (Install::isInstalled() == false) { 
            return $this->loadInstallPage($request,$response,$args);
        }
        Arikaim::page()->load('page-not-found',$args); 
        return $response->withStatus(404);
    }

    public function loadInstallPage($request, $response, $args = [])
    {
        if (Install::isInstalled() == false) {        
            $params = ['page' => 'system:install'];                     
            return $this->loadPage($request,$response,Arrays::merge($params,$args));
        }
        Arikaim::errors()->addError('INSTALLED_ERROR');
        $params = ['page' => 'system-error'];     
        return $this->loadPage($request,$response,Arrays::merge($params,$args));
    }
}
