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

use Arikaim\Core\View\Html\Page;
use Arikaim\Core\Db\Model;
use Arikaim\Core\System\Install;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Controlers\Controler;
use Arikaim\Core\System\Config;

class PageLoader extends Controler 
{
    protected $page;

    public function __construct() 
    {
        parent::__construct();
        $this->page = new Page();
    }

    public function loadPage($request, $response, $args)
    {
        if (Arikaim::errors()->errorsCount() > 0) {
            // load errors page
            if ($this->page->hasPage("system-error") == false) {
                // load system page
                $this->page->loadPage("system:system-error",$args);
            } else {
                // load from default template
                $this->page->loadPage("system-error",$args);
            }       
        }
        if (isset($args['page']) == false) {
            $page_name = "home";
        } else {
            $page_name = $args['page'];
        }
        $this->page->loadPage($page_name,$args);       
        return $response;
    }

    public function loadControlPanel($request, $response, $args) 
    {
        $user = Model::User();         
        $loged_in = $user->isLogedAdminUser();
        
        if ($loged_in == false) {            
            Arikaim::cookies()->set("token",null);           
        }
        if (Install::isInstalled() == false) {
            $this->page->loadPage("system:install",$args);
        } else {            
            $this->page->loadPage("system:admin",['loged_in' => $loged_in]);
        }
        return $response;       
    }

    public function pageNotFound($request, $response, $args = []) 
    {            
        if ($this->page->pageExists("system:page-not-found") == true) {
            $this->page->loadPage("system:page-not-found",$args);  
            return $this->response->withStatus(404);
        }         
        $this->response->withStatus(404)->write('Page not found');
        return $this->response;
    }
}
