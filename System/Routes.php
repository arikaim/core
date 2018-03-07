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

use Arikaim\Core\Middleware\SessionAuthentication;
use Arikaim\Core\Middleware\JwtAuthentication;
use Arikaim\Core\Controlers\Controler;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Arikaim;

class Routes 
{
    public static function mapExtensionsRoutes($app)
    {
        if (Arikaim::errors()->hasError("DB_CONNECTION_ERROR") == true) {
            return $app;
        }
        $routes = Model::Routes()->getRoutes(Model::getConstant('Routes','ACTIVE'));
      
        if (is_array($routes) == true) {
            foreach($routes as $item) {
                $path = $item['path'] . $item['pattern'];
                $methods = explode(',',$item['method']);
                $handler = $item['handler_class'] . ":" . $item['handler_method'];
                $middleware = Factory::createAuthMiddleware($item['auth']);                 
                $route = $app->map($methods,$path,$handler);
                if ($middleware != null) {
                    $route->add($middleware);
                }
            }
        }
        return $app;
    }

    public static function mapSystemRoutes($app)
    {
        // site stats middleware
        if (Arikaim::options('logger.stats') == true) {                        
            $app->add(new \Arikaim\Core\Middleware\SiteStats);   
        }    
        // Middleware for sanitize request body and client ip
        $app->add(new \Arikaim\Core\Middleware\CoreMiddleware());  

        $session_auth = new SessionAuthentication();
        $jwt_auth =  new JwtAuthentication();

        $api_controles_namespace = Controler::getApiControlersNamespace();

        // Install page
        $app->get('/install/',Controler::getControlersNamespace() . "\Pages\PageLoader:loadInstallPage");

        //Api Client
        $app->post('/api/create/token/',"$api_controles_namespace\ApiClient:createToken");
        $app->post('/api/verify/request/',"$api_controles_namespace\ApiClient:verifyRequest");

        // Session
        $app->put('/api/session/',"$api_controles_namespace\SessionApi:setValue")->add($jwt_auth);
        $app->get('/api/session/',"$api_controles_namespace\SessionApi:getInfo")->add($session_auth);
        $app->get('/api/session/restart/',"$api_controles_namespace\SessionApi:restart")->add($jwt_auth);
        // UI Component       
        $app->get('/api/ui/component/{name}[/{params:.*}]',"$api_controles_namespace\Ui\ComponentApi:loadComponent"); //->add($session_auth);
        // UI Page  
        $app->get('/api/ui/page/{name}',"$api_controles_namespace\Ui\PageApi:loadPage")->add($session_auth);
        $app->get('/api/ui/page/properties/',"$api_controles_namespace\Ui\PageApi:loadPageProperties")->add($session_auth);  
    
        // Control Panel
        $app->get('/admin[/]',Controler::getControlersNamespace() . "\Pages\PageLoader:loadControlPanel");
        // Install
        $app->post('/admin/api/install/',"$api_controles_namespace\AdminApi:install")->add($session_auth);    
        // Update
        $app->get('/admin/api/update/',"$api_controles_namespace\AdminApi:update");//->add($jwt_auth);  
        $app->get('/admin/api/update/check',"$api_controles_namespace\AdminApi:updateCheckVersion")->add($jwt_auth);    
        // Admin user
        $app->post('/admin/api/user/login/',"$api_controles_namespace\UsersApi:adminLogin")->add($session_auth); 
        $app->post('/admin/api/user/reset-passord',"$api_controles_namespace\UsersApi:resetPassword")->add($jwt_auth); 
        $app->post('/admin/api/user/',"$api_controles_namespace\UsersApi:changeDetails")->add($jwt_auth);
        $app->get('/admin/api/user/logout/',"$api_controles_namespace\UsersApi:logout");
        // Languages
        $app->post('/admin/api/language/',"$api_controles_namespace\LanguageApi:add")->add($jwt_auth); 
        $app->delete('/admin/api/language/{uuid}',"$api_controles_namespace\LanguageApi:remove")->add($jwt_auth); 
        $app->put('/admin/api/language/change/{language_code}',"$api_controles_namespace\LanguageApi:changeLanguage"); 
        $app->put('/admin/api/language/status/{uuid}/{status}',"$api_controles_namespace\LanguageApi:setStatus")->add($jwt_auth); 
        $app->put('/admin/api/language/default/{uuid}',"$api_controles_namespace\LanguageApi:setDefault")->add($jwt_auth); 
        $app->put('/admin/api/language/move/{uuid}/{after_uuid}',"$api_controles_namespace\LanguageApi:changeOrder")->add($jwt_auth); 
        // Extensions
        $app->put('/admin/api/extension/install/{name}',"$api_controles_namespace\ExtensionsApi:install")->add($jwt_auth); 
        $app->put('/admin/api/extension/status/{name}/{status}',"$api_controles_namespace\ExtensionsApi:changeStatus")->add($jwt_auth); 
        $app->put('/admin/api/extension/uninstall/{name}',"$api_controles_namespace\ExtensionsApi:unInstall")->add($jwt_auth); 
        // Templates
        $app->put('/admin/api/template/current/{name}',"$api_controles_namespace\TemplatesApi:setCurrent")->add($jwt_auth); 
        $app->put('/admin/api/template/theme/current/',"$api_controles_namespace\TemplatesApi:setCurrentTheme")->add($jwt_auth); 
        // Options
        $app->get('/admin/api/options/{key}',"$api_controles_namespace\OptionsApi:get")->add($jwt_auth);
        $app->put('/admin/api/options/',"$api_controles_namespace\OptionsApi:save")->add($jwt_auth);
        $app->post('/admin/api/options/',"$api_controles_namespace\OptionsApi:saveOptions")->add($jwt_auth);
        // Logs
        $app->delete('/admin/api/logs/',"$api_controles_namespace\AdminApi:clearLogs")->add($jwt_auth);
        // Jobs
        $app->delete('/admin/api/jobs/worker',"$api_controles_namespace\AdminApi:deleteQueueWorkerJobs")->add($jwt_auth);
        $app->get('/admin/api/jobs/worker/update',"$api_controles_namespace\AdminApi:updateQueueWorkerJobs")->add($jwt_auth);
        // Modules
        $app->get('/admin/api/modules/update',"$api_controles_namespace\AdminApi:updateModules")->add($jwt_auth);
        // Mailer
        $app->get('/admin/api/mailer/test/email',"$api_controles_namespace\AdminApi:sendTestEmail")->add($jwt_auth);

        $app = Self::mapExtensionsRoutes($app);
        return $app;
    }
}
