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

    /**
     * Show template page
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @param array $args route params
     * @return void
     */
    public function loadTemplatePage($request, $response, $data, $args)
    {
        $route = $request->getAttribute('route');  
        if (is_object($route) == false) {
            return Arikaim::page()->loadPage("system:system-error",$args);
        }    
        $pattern = $route->getPattern();
        $model = Model::Routes()->getRoute('GET',$pattern);
      
        if ($model == false) {
            return $this->pageNotFound($request,$response,$data);
        }
        $params = ['page' => $model->template_page];
        return $this->loadPage($request,$response,Arrays::merge($params,$args));
    }

    public function loadPage($request, $response, $data)
    {
        if (isset($data['page']) == true) {
            $page_name = $data['page'];           
        } else {
            return $this->pageNotFound($request,$response,$data);
        }
       
        $language = $this->getPageLanguage($data);
        if ($language == false) {
            return $this->pageNotFound($request,$response,$data);
        }

        if (Arikaim::page()->has($page_name) == true) {
            return Arikaim::page()->loadPage($page_name,$data,$language);             
        } 
        return $this->pageNotFound($request,$response,$data);
    }

    public function getPageLanguage($data)
    {
        if (isset($data['language']) == true) {
            $language = $data['language'];
            if (Model::Language()->has($language,true) == false) {
                return false;
            }
            Template::setLanguage($language);
        }     

        return Template::getLanguage();
    }

    public function loadControlPanel($request, $response, $data = []) 
    {   
        $language = $this->getPageLanguage($data);
        if (Install::isInstalled() == false) { 
            return $this->loadInstallPage($request,$response,$data);
        }
        $user = Model::Users()->getLogedUser();    
        if ($user != false) {
            $loged_in = Arikaim::access()->hasControlPanelAccess($user->uuid);
        } else {
            $loged_in = false;
        }        
        $params = ['page' => 'system:admin','loged_in' => $loged_in];
        return $this->loadPage($request,$response,Arrays::merge($params,$data->toArray()));    
    }

    public function loadChangePassword($request, $response, $data = [])
    {
        if (isset($data['code']) == true) {
            $code = $data['code'];
            $params = ['page' => 'system:admin/change-password'];
            return $this->loadPage($request,$response,Arrays::merge($params,$data));
        } 
        return Arikaim::page()->loadPage("system:system-error",$data->toArray());
    }

    public function pageNotFound($request, $response, $data = []) 
    {                  
        if (Install::isInstalled() == false) { 
            return $this->loadInstallPage($request,$response,$data);
        }       
        $response = Arikaim::page()->loadPage('system:page-not-found',$data); 
        return $response->withStatus(404);
    }

    public function loadInstallPage($request, $response, $data = [])
    {
        if (Install::isInstalled() == false) {        
            $params = ['page' => 'system:install'];                     
            return $this->loadPage($request,$response,Arrays::merge($params,$data->toArray()));
        }
        Arikaim::errors()->addError('INSTALLED_ERROR');
        $params = ['page' => 'system:system-error'];     
        return $this->loadPage($request,$response,Arrays::merge($params,$data->toArray()));
    }
}
