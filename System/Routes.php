<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System;

use Illuminate\Database\Capsule\Manager;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Traits\Db\Status;
use Arikaim\Core\Utils\Factory;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Packages\Module\ModulePackage;
use Arikaim\Core\System\Path;

/**
 * Routes
 */
class Routes 
{
    /**
     * Map routes
     *
     * @param App $app
     * @return App
     */
    public static function mapRoutes($app)
    {
        if (Arikaim::errors()->hasError() == true) {
            return $app;
        }

        $routes = Arikaim::cache()->fetch('routes.list');
        if (is_array($routes) == false) {
            $routes = Model::Routes()->getRoutes(Status::$ACTIVE);  
            Arikaim::cache()->save('routes.list',$routes,4);         
        }

        foreach($routes as $item) {
            $methods = explode(',',$item['method']);
            $handler = $item['handler_class'] . ":" . $item['handler_method'];   
            $route = $app->map($methods,$item['pattern'],$handler);
            // auth middleware
            if ($item['auth'] > 0) {
                $middleware = Arikaim::auth()->middleware($item['auth']);    
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

    /**
     * Map core routes
     *
     * @param App $app
     * @return App
     */
    public static function mapSystemRoutes($app)
    {
        $api_namespace = Path::API_CONTROLLERS_NAMESPACE;
        $controller_namespace = Path::CONTROLLERS_NAMESPACE;

        // Middleware for sanitize request body and client ip
        $app->add(new \Arikaim\Core\Middleware\CoreMiddleware());        
        // Load middleware modules
        $app = Self::loadModulesMiddleware($app);
      
        $session_auth = Arikaim::auth()->middleware('session');
        // Control Panel
        $app->get('/admin/[{language:[a-z]{2}}/]',"$controller_namespace\PageLoader:loadControlPanel");       
        // Api Access
        $app->post('/core/api/create/token/',"$api_namespace\Client:createToken");
        $app->post('/core/api/verify/request/',"$api_namespace\Client:verifyRequest");      
        // UI Component
        $app->get('/core/api/ui/component/properties/{name}[/{params:.*}]',"$api_namespace\Ui\Component:componentProperties");
        $app->get('/core/api/ui/component/details/{name}[/{params:.*}]',"$api_namespace\Ui\Component:componentDetails")->add($session_auth);
        $app->get('/core/api/ui/component/{name}[/{params:.*}]',"$api_namespace\Ui\Component:loadComponent");      
        $app->post('/core/api/ui/library/upload',"$api_namespace\Ui\Library:upload")->add($session_auth);
        // UI Page  
        $app->get('/core/api/ui/page/{name}',"$api_namespace\Ui\Page:loadPage");
        $app->get('/core/api/ui/page/properties/',"$api_namespace\Ui\Page:loadPageProperties");  
        // Paginator 
        $app->group('/core/api/ui/paginator',function($app) use($api_namespace) {  
            $app->put('/page-size',"$api_namespace\Ui\Paginator:setPageSize");
            $app->put('/page',"$api_namespace\Ui\Paginator:setPage");
            $app->get('/[{namespace}]',"$api_namespace\Ui\Paginator:getPage");
            $app->put('/view/type',"$api_namespace\Ui\Paginator:setViewType");
            $app->get('/view/type/[{namespace}]',"$api_namespace\Ui\Paginator:getViewType");
            $app->delete('/{namespace}',"$api_namespace\Ui\Paginator:remove");
        });
        // Search 
        $app->group('/core/api/ui/search',function($app) use($api_namespace) { 
            $app->put('/',"$api_namespace\Ui\Search:setSearch"); 
            $app->put('/condition/[{namespace}]',"$api_namespace\Ui\Search:setSearchCondition");      
            $app->delete('/condition/{field}/[{namespace}]',"$api_namespace\Ui\Search:deleteSearchCondition");
            $app->delete('/[{namespace}]',"$api_namespace\Ui\Search:clearSearch");
        });
        // Order by column
        $app->group('/core/api/ui/order',function($app) use($api_namespace) { 
            $app->put('/[{namespace}]',"$api_namespace\Ui\OrderBy:setOrderBy"); 
            $app->get('/[{namespace}]',"$api_namespace\Ui\OrderBy:getOrderBy");      
            $app->delete('/[{namespace}]',"$api_namespace\Ui\OrderBy:deleteOrderBy");
        })->add($session_auth);        
        // Position
        $app->group('/core/api/ui/position',function($app) use($api_namespace) { 
            $app->put('/shift',"$api_namespace\Ui\Position:shift");
            $app->put('/swap',"$api_namespace\Ui\Position:swap");
        })->add($session_auth);              
        // Control Panel user
        $app->group('/core/api/user',function($app) use($api_namespace) {  
            $app->post('/login/',"$api_namespace\Users:adminLogin");
            $app->post('/password/recovery/',"$api_namespace\Users:passwordRecovery");
            $app->post('/password/change/',"$api_namespace\Users:changePassword");
            $app->get('/logout/',"$api_namespace\Users:logout");
        });
        // Change password page
        $app->get('/admin/change-password/{code}/[{language}/]',"$controller_namespace\PageLoader:loadChangePassword");
        $app->post('/core/api/user/',"$api_namespace\Users:changeDetails")->add($session_auth);
        // Languages  
        $app->group('/core/api/language',function($app) use($api_namespace) {      
            $app->post('/add',"$api_namespace\Language:add");
            $app->put('/update',"$api_namespace\Language:update");
            $app->delete('/{uuid}',"$api_namespace\Language:remove");
            $app->put('/change/{language_code}',"$api_namespace\Language:changeLanguage"); 
            $app->put('/status',"$api_namespace\Language:setStatus");
            $app->put('/default',"$api_namespace\Language:setDefault");
        })->add($session_auth);        
        // Options
        $app->group('/core/api/options',function($app) use($api_namespace) {
            $app->get('/{key}',"$api_namespace\Options:get");
            $app->put('/',"$api_namespace\Options:save");
            $app->post('/',"$api_namespace\Options:saveOptions");
        })->add($session_auth);
        // Queue
        $app->group('/core/api/queue',function($app) use($api_namespace) {
            $app->put('/cron/install',"$api_namespace\Queue:installCron");
            $app->delete('/cron/uninstall',"$api_namespace\Queue:unInstallCron");
            $app->delete('/jobs',"$api_namespace\Queue:deleteJobs");
            $app->put('/worker/start',"$api_namespace\Queue:startWorker");
            $app->delete('/worker/stop',"$api_namespace\Queue:stopWorker");
        })->add($session_auth);
        // Jobs
        $app->group('/core/api/jobs',function($app) use($api_namespace) {
            $app->delete('/delete/{uuid}',"$api_namespace\Jobs:deleteJob");
            $app->put('/status',"$api_namespace\Jobs:setStatus");          
        })->add($session_auth);
        // Templates
        $app->group('/core/api/template',function($app) use($api_namespace) {
            $app->put('/current',"$api_namespace\Templates:setCurrent");
            $app->put('/update',"$api_namespace\Templates:update");
            $app->put('/theme/current',"$api_namespace\Templates:setCurrentTheme");
        })->add($session_auth);
        // Extensions
        $app->group('/core/api/extension',function($app) use($api_namespace) {
            $app->put('/install',"$api_namespace\Extensions:install");
            $app->put('/status',"$api_namespace\Extensions:setStatus");
            $app->put('/uninstall',"$api_namespace\Extensions:unInstall");
            $app->put('/update',"$api_namespace\Extensions:update");
        })->add($session_auth);
        // Modules
        $app->group('/core/api/module',function($app) use($api_namespace) {
            $app->put('/install',"$api_namespace\Modules:installModule");
            $app->put('/disable',"$api_namespace\Modules:disableModule");
            $app->delete('/uninstall/{name}',"$api_namespace\Modules:unInstallModule");
            $app->put('/enable',"$api_namespace\Modules:enableModule");
            $app->put('/update',"$api_namespace\Modules:updateModule");
            $app->post('/config',"$api_namespace\Modules:saveConfig");
        })->add($session_auth);
        // Drivers
        $app->group('/core/api/driver',function($app) use($api_namespace) { 
            $app->put('/status',"$api_namespace\Drivers:setStatus");          
            $app->get('/config/{name}',"$api_namespace\Drivers:readConfig");
            $app->put('/config',"$api_namespace\Drivers:saveConfig");
        })->add($session_auth);
        // Update
        $app->group('/core/api/update',function($app) use($api_namespace) {
            $app->get('/',"$api_namespace\Update:update");
            $app->get('/check',"$api_namespace\Update:checkVersion"); 
        })->add($session_auth);
        // Session
        $app->group('/core/api/session',function($app) use($api_namespace) {        
            $app->put('/recreate',"$api_namespace\Session:recreate");
            $app->put('/restart',"$api_namespace\Session:restart");
        })->add($session_auth);
        // Access tokens
        $app->group('/core/api/tokens',function($app) use($api_namespace) {
            $app->delete('/delete/{token}',"$api_namespace\AccessTokens:delete");
            $app->delete('/delete/expired/{uuid}',"$api_namespace\AccessTokens:deleteExpired");
        })->add($session_auth);
        // Settings
        $app->group('/core/api/settings',function($app) use($api_namespace) {
            $app->put('/debug',"$api_namespace\Settings:setDebug");
        })->add($session_auth);
        // Mailer
        $app->group('/core/api/mailer',function($app) use($api_namespace) {
            $app->get('/test/email',"$api_namespace\Mailer:sendTestEmail");
        })->add($session_auth);
        // Cache
        $app->group('/core/api/cache',function($app) use($api_namespace) {
            $app->delete('/clear',"$api_namespace\Cache:clear");
            $app->put('/enable',"$api_namespace\Cache:enable");
            $app->put('/disable',"$api_namespace\Cache:disable");
        })->add($session_auth);
        // Logs
        $app->group('/core/api/logs',function($app) use($api_namespace) {
            $app->delete('/clear',"$api_namespace\Logger:clear");
        })->add($session_auth);

        $app->get('/core/api/session/',"$api_namespace\Session:getInfo");
        // Install
        $app->post('/core/api/install/',"$api_namespace\Install:install");
        // Install page
        $app->get('/install/',"$controller_namespace\\PageLoader:loadInstallPage");
      
        return Self::mapRoutes($app);     
    }
}
