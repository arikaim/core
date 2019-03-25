<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System;

use Illuminate\Database\Capsule\Manager;

use Arikaim\Core\Middleware\SessionAuthentication;
use Arikaim\Core\Middleware\JwtAuthentication;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Traits\Db\Status;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Packages\Module\ModulePackage;
use Arikaim\Core\Cache\Cache;
use Arikaim\Core\System\Path;

class Routes 
{
    public static function mapRoutes($app)
    {
        if (Arikaim::errors()->hasError() == true) {
            return $app;
        }

        $routes = Arikaim::cache()->fetch('routes.list');
        if (is_array($routes) == false) {
            $routes = Model::Routes()->getRoutes(Status::ACTIVE());  
            Arikaim::cache()->save('routes.list',$routes,4);         
        }

        foreach($routes as $item) {
            $methods = explode(',',$item['method']);
            $handler = $item['handler_class'] . ":" . $item['handler_method'];          
            $route = $app->map($methods,$item['pattern'],$handler);
            // auth middleware
            if ($item['auth'] > 1) {
                $middleware = Factory::createAuthMiddleware($item['auth']);    
                if ($middleware != null) {
                    $route->add($middleware);
                }
            }  
        }       
        return $app;
    }

    /**
     * Load core modules middleware
     *
     * @param object $app
     * @return object $app
     */
    public static function loadModulesMiddleware($app)
    {
        $modules = Arikaim::cache()->fetch('middleware.list');
        if (is_array($modules) == false) {   
            if (Manager::schema()->hasTable('modules') == false) {
                return $app;
            }            
            $modules = Model::Modules()->getList(ModulePackage::getTypeId('middleware'),1);         
            Arikaim::cache()->save('middleware.list',$modules,2);    
        }    

        foreach ($modules as $module) {             
            $instance = Factory::createModule($module['name'],$module['class']);
            if (is_object($instance) == true) {
                $app->add($instance);  
            }         
        }
        return $app;
    }

    public static function mapSystemRoutes($app)
    {
        $api_namespace = Path::API_CONTROLERS_NAMESPACE;
        $controller_namespace = Path::CONTROLERS_NAMESPACE;

        // site stats middleware
        if (Arikaim::options('logger.stats') == true) {                        
            $app->add(new \Arikaim\Core\Middleware\SiteStats);   
        }    
        // Middleware for sanitize request body and client ip
        $app->add(new \Arikaim\Core\Middleware\CoreMiddleware());        
        // Load middleware modules
        $app = Self::loadModulesMiddleware($app);
      
        $session_auth = new SessionAuthentication();
        $jwt_auth = new JwtAuthentication();

        //Api Access
        $app->post('/core/api/create/token/',"$api_namespace\Client:createToken");
        $app->post('/core/api/verify/request/',"$api_namespace\Client:verifyRequest");      
        // UI Component       
        $app->get('/core/api/ui/component/{name}[/{params:.*}]',"$api_namespace\Ui\Component:loadComponent");
        $app->get('/core/api/ui/component/details/{name}[/]',"$api_namespace\Ui\Component:componentDetails")->add($jwt_auth);
        // UI Page  
        $app->get('/core/api/ui/page/{name}',"$api_namespace\Ui\Page:loadPage")->add($session_auth);
        $app->get('/core/api/ui/page/properties/',"$api_namespace\Ui\Page:loadPageProperties")->add($session_auth);    
        // Control Panel
        $app->get('/admin/[{language:[a-z]{2}}/]',"$controller_namespace\Pages\PageLoader:loadControlPanel");      
        
        // Control Panel user
        $app->group('/core/api/user',function($app) use($api_namespace) {  
            $app->post('/login/',"$api_namespace\Users:adminLogin");
            $app->post('/password/recovery/',"$api_namespace\Users:passwordRecovery");
            $app->post('/password/change/',"$api_namespace\Users:changePassword");
            $app->get('/logout/',"$api_namespace\Users:logout");
        });
        // Change password page
        $app->get('/admin/change-password/{code}/[{language}/]',"$controller_namespace\Pages\PageLoader:loadChangePassword");
        $app->post('/core/api/user/',"$api_namespace\Users:changeDetails")->add($jwt_auth);
        // Languages  
        $app->group('/core/api/language',function($app) use($api_namespace) {      
            $app->post('/',"$api_namespace\Language:add");
            $app->delete('/{uuid}',"$api_namespace\Language:remove");
            $app->put('/change/{language_code}',"$api_namespace\Language:changeLanguage"); 
            $app->put('/status/{uuid}/{status}',"$api_namespace\Language:setStatus");
            $app->put('/default/{uuid}',"$api_namespace\Language:setDefault");
            $app->put('/move/{uuid}/{after_uuid}',"$api_namespace\Language:changeOrder");
        })->add($jwt_auth);        
        // Options
        $app->group('/core/api/options',function($app) use($api_namespace) {
            $app->get('/{key}',"$api_namespace\Options:get");
            $app->put('/',"$api_namespace\Options:save");
            $app->post('/',"$api_namespace\Options:saveOptions");
        })->add($jwt_auth);
        // Queue
        $app->group('/core/api/queue',function($app) use($api_namespace) {
            $app->delete('/worker',"$api_namespace\Queue:deleteJobs");
            $app->get('/worker/update',"$api_namespace\Queue:updateJobs");
        })->add($jwt_auth);
        // Templates
        $app->group('/core/api/template',function($app) use($api_namespace) {
            $app->put('/current/{name}',"$api_namespace\Templates:setCurrent");
            $app->put('/install/{name}',"$api_namespace\Templates:install");
            $app->put('/theme/current/',"$api_namespace\Templates:setCurrentTheme");
        })->add($jwt_auth);
        // Extensions
        $app->group('/core/api/extension',function($app) use($api_namespace) {
            $app->put('/install/{name}',"$api_namespace\Extensions:install");
            $app->put('/status/{name}/{status}',"$api_namespace\Extensions:changeStatus");
            $app->put('/uninstall/{name}',"$api_namespace\Extensions:unInstall");
        })->add($jwt_auth);
        // Modules
        $app->group('/core/api/module',function($app) use($api_namespace) {
            $app->put('/install/{name}',"$api_namespace\Modules:installModule");
            $app->put('/disable/{name}',"$api_namespace\Modules:disableModule");
            $app->delete('/uninstall/{name}',"$api_namespace\Modules:unInstallModule");
            $app->put('/enable/{name}',"$api_namespace\Modules:enableModule");
        })->add($jwt_auth);
        // Update
        $app->group('/core/api/update',function($app) use($api_namespace) {
            $app->get('/',"$api_namespace\Update:update");
            $app->get('/check',"$api_namespace\Update:checkVersion"); 
        })->add($jwt_auth);
        // Session
        $app->group('/core/api/session',function($app) use($api_namespace) {
            $app->put('/',"$api_namespace\Session:setValue");
            $app->get('/restart/',"$api_namespace\Session:restart");
        })->add($jwt_auth);
       
        $app->get('/core/api/session/',"$api_namespace\Session:getInfo")->add($session_auth);
        // Install
        $app->post('/core/api/install/',"$api_namespace\Install:install")->add($session_auth); 
        // Install page
        $app->get('/install/',"$controller_namespace\\Pages\\PageLoader:loadInstallPage");
        // Change system settigns
        $app->put('/core/api/settings/',"$api_namespace\Settings:save")->add($jwt_auth);
        // Logs
        $app->delete('/core/api/logs/',"$api_namespace\Logger:clear")->add($jwt_auth);
        // Mailer
        $app->get('/core/api/mailer/test/email',"$api_namespace\Mailer:sendTestEmail")->add($jwt_auth);
        
        $app = Self::mapRoutes($app);
        return $app;
    }
}
