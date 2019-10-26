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

use FastRoute\RouteParser\Std;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Traits\Db\Status;
use Arikaim\Core\Arikaim;
use Arikaim\Core\System\Path;
use \InvalidArgumentException;

/**
 * Routes
 */
class Routes 
{
    /**
     * Map routes
     *     
     * @return boolean
     */
    public static function mapRoutes()
    {
        if (Arikaim::db()->isValidConnection() == false) {
            return false;
        }

        if (Arikaim::errors()->hasError() == true) {
            return false;
        }

        $routes = Arikaim::cache()->fetch('routes.list');
        if (is_array($routes) == false) {
            $routes = Model::Routes()->getRoutes(Status::$ACTIVE);  
            Arikaim::cache()->save('routes.list',$routes,4);         
        }

        foreach($routes as $item) {
            $methods = explode(',',$item['method']);
            $handler = $item['handler_class'] . ":" . $item['handler_method'];   
            $route = Arikaim::$app->map($methods,$item['pattern'],$handler);
            // auth middleware
            if ($item['auth'] > 0) {
                $middleware = Arikaim::auth()->middleware($item['auth']);    
                if ($middleware != null) {
                    $route->add($middleware);
                }
            }                                
        }          

        return true;
    }

    /**
     * Map core routes
     *   
     * @return boolean
     */
    public static function mapSystemRoutes()
    {
        $api_namespace = Path::API_CONTROLLERS_NAMESPACE;
        $controller_namespace = Path::CONTROLLERS_NAMESPACE;

        $session_auth = Arikaim::auth()->middleware('session');
        // Control Panel
        Arikaim::$app->get('/admin/[{language:[a-z]{2}}/]',"$controller_namespace\PageLoader:loadControlPanel");    
        // Api Access
        Arikaim::$app->post('/core/api/create/token/',"$api_namespace\Client:createToken");
        Arikaim::$app->post('/core/api/verify/request/',"$api_namespace\Client:verifyRequest");      
        // UI Component
        Arikaim::$app->get('/core/api/ui/component/properties/{name}[/{params:.*}]',"$api_namespace\Ui\Component:componentProperties");
        Arikaim::$app->get('/core/api/ui/component/details/{name}[/{params:.*}]',"$api_namespace\Ui\Component:componentDetails")->add($session_auth);
        Arikaim::$app->get('/core/api/ui/component/{name}[/{params:.*}]',"$api_namespace\Ui\Component:loadComponent");      
        Arikaim::$app->post('/core/api/ui/library/upload',"$api_namespace\Ui\Library:upload")->add($session_auth);
        // UI Page  
        Arikaim::$app->get('/core/api/ui/page/{name}',"$api_namespace\Ui\Page:loadPage");
        Arikaim::$app->get('/core/api/ui/page/properties/',"$api_namespace\Ui\Page:loadPageProperties");  
        // Paginator 
        Arikaim::$app->group('/core/api/ui/paginator',function($group) use($api_namespace) {  
            $group->put('/page-size',"$api_namespace\Ui\Paginator:setPageSize");
            $group->put('/page',"$api_namespace\Ui\Paginator:setPage");
            $group->get('/[{namespace}]',"$api_namespace\Ui\Paginator:getPage");
            $group->put('/view/type',"$api_namespace\Ui\Paginator:setViewType");
            $group->get('/view/type/[{namespace}]',"$api_namespace\Ui\Paginator:getViewType");
            $group->delete('/{namespace}',"$api_namespace\Ui\Paginator:remove");
        });     
        // Search 
        Arikaim::$app->group('/core/api/ui/search',function($group) use($api_namespace) { 
            $group->put('/',"$api_namespace\Ui\Search:setSearch"); 
            $group->put('/condition/[{namespace}]',"$api_namespace\Ui\Search:setSearchCondition");      
            $group->delete('/condition/{field}/[{namespace}]',"$api_namespace\Ui\Search:deleteSearchCondition");
            $group->delete('/[{namespace}]',"$api_namespace\Ui\Search:clearSearch");
        });
        // Order by column
        Arikaim::$app->group('/core/api/ui/order',function($group) use($api_namespace) { 
            $group->put('/[{namespace}]',"$api_namespace\Ui\OrderBy:setOrderBy"); 
            $group->get('/[{namespace}]',"$api_namespace\Ui\OrderBy:getOrderBy");      
            $group->delete('/[{namespace}]',"$api_namespace\Ui\OrderBy:deleteOrderBy");
        })->add($session_auth);        
        // Position
        Arikaim::$app->group('/core/api/ui/position',function($group) use($api_namespace) { 
            $group->put('/shift',"$api_namespace\Ui\Position:shift");
            $group->put('/swap',"$api_namespace\Ui\Position:swap");
        })->add($session_auth);              
        // Control Panel user
        Arikaim::$app->group('/core/api/user',function($group) use($api_namespace) {  
            $group->post('/login/',"$api_namespace\Users:adminLogin");
            $group->post('/password/recovery/',"$api_namespace\Users:passwordRecovery");
            $group->post('/password/change/',"$api_namespace\Users:changePassword");
            $group->get('/logout/',"$api_namespace\Users:logout");
        });
        // Change password page
        Arikaim::$app->get('/admin/change-password/{code}/[{language}/]',"$controller_namespace\PageLoader:loadChangePassword");
        Arikaim::$app->post('/core/api/user/',"$api_namespace\Users:changeDetails")->add($session_auth);
        // Languages  
        Arikaim::$app->group('/core/api/language',function($group) use($api_namespace) {      
            $group->post('/add',"$api_namespace\Language:add");
            $group->put('/update',"$api_namespace\Language:update");
            $group->delete('/{uuid}',"$api_namespace\Language:remove");
            $group->put('/change/{language_code}',"$api_namespace\Language:changeLanguage"); 
            $group->put('/status',"$api_namespace\Language:setStatus");
            $group->put('/default',"$api_namespace\Language:setDefault");
        })->add($session_auth);        
        // Options
        Arikaim::$app->group('/core/api/options',function($group) use($api_namespace) {
            $group->get('/{key}',"$api_namespace\Options:get");
            $group->put('/',"$api_namespace\Options:save");
            $group->post('/',"$api_namespace\Options:saveOptions");
        })->add($session_auth);
        // Queue
        Arikaim::$app->group('/core/api/queue',function($group) use($api_namespace) {
            $group->put('/cron/install',"$api_namespace\Queue:installCron");
            $group->delete('/cron/uninstall',"$api_namespace\Queue:unInstallCron");
            $group->delete('/jobs',"$api_namespace\Queue:deleteJobs");
            $group->put('/worker/start',"$api_namespace\Queue:startWorker");
            $group->delete('/worker/stop',"$api_namespace\Queue:stopWorker");
        })->add($session_auth);
        // Jobs
        Arikaim::$app->group('/core/api/jobs',function($group) use($api_namespace) {
            $group->delete('/delete/{uuid}',"$api_namespace\Jobs:deleteJob");
            $group->put('/status',"$api_namespace\Jobs:setStatus");          
        })->add($session_auth);
        // Templates
        Arikaim::$app->group('/core/api/template',function($group) use($api_namespace) {
            $group->put('/current',"$api_namespace\Templates:setCurrent");
            $group->put('/update',"$api_namespace\Templates:update");
            $group->put('/theme/current',"$api_namespace\Templates:setCurrentTheme");
        })->add($session_auth);
        // Extensions
        Arikaim::$app->group('/core/api/extension',function($group) use($api_namespace) {
            $group->put('/install',"$api_namespace\Extensions:install");
            $group->put('/status',"$api_namespace\Extensions:setStatus");
            $group->put('/uninstall',"$api_namespace\Extensions:unInstall");
            $group->put('/update',"$api_namespace\Extensions:update");
        })->add($session_auth);
        // Modules
        Arikaim::$app->group('/core/api/module',function($group) use($api_namespace) {
            $group->put('/install',"$api_namespace\Modules:installModule");
            $group->put('/disable',"$api_namespace\Modules:disableModule");
            $group->delete('/uninstall/{name}',"$api_namespace\Modules:unInstallModule");
            $group->put('/enable',"$api_namespace\Modules:enableModule");
            $group->put('/update',"$api_namespace\Modules:updateModule");
            $group->post('/config',"$api_namespace\Modules:saveConfig");
        })->add($session_auth);
        // Drivers
        Arikaim::$app->group('/core/api/driver',function($group) use($api_namespace) { 
            $group->put('/status',"$api_namespace\Drivers:setStatus");          
            $group->get('/config/{name}',"$api_namespace\Drivers:readConfig");
            $group->put('/config',"$api_namespace\Drivers:saveConfig");
        })->add($session_auth);
        // Update
        Arikaim::$app->group('/core/api/update',function($group) use($api_namespace) {
            $group->get('/',"$api_namespace\Update:update");
            $group->get('/check',"$api_namespace\Update:checkVersion"); 
        })->add($session_auth);
        // Session
        Arikaim::$app->group('/core/api/session',function($group) use($api_namespace) {        
            $group->put('/recreate',"$api_namespace\Session:recreate");
            $group->put('/restart',"$api_namespace\Session:restart");
        })->add($session_auth);
        // Access tokens
        Arikaim::$app->group('/core/api/tokens',function($group) use($api_namespace) {
            $group->delete('/delete/{token}',"$api_namespace\AccessTokens:delete");
            $group->delete('/delete/expired/{uuid}',"$api_namespace\AccessTokens:deleteExpired");
        })->add($session_auth);
        // Settings
        Arikaim::$app->group('/core/api/settings',function($group) use($api_namespace) {
            $group->put('/debug',"$api_namespace\Settings:setDebug");
        })->add($session_auth);
        // Mailer
        Arikaim::$app->group('/core/api/mailer',function($group) use($api_namespace) {
            $group->get('/test/email',"$api_namespace\Mailer:sendTestEmail");
        })->add($session_auth);
        // Cache
        Arikaim::$app->group('/core/api/cache',function($group) use($api_namespace) {
            $group->delete('/clear',"$api_namespace\Cache:clear");
            $group->put('/enable',"$api_namespace\Cache:enable");
            $group->put('/disable',"$api_namespace\Cache:disable");
        })->add($session_auth);
        // Logs
        Arikaim::$app->group('/core/api/logs',function($group) use($api_namespace) {
            $group->delete('/clear',"$api_namespace\Logger:clear");
        })->add($session_auth);
        // Orm
        Arikaim::$app->group('/core/api/orm',function($group) use($api_namespace) {
            $group->put('/relation/delete',"$api_namespace\Orm:deleteRelation");
            $group->post('/relation',"$api_namespace\Orm:addRelation");
            $group->put('/options',"$api_namespace\Orm:saveOptions");
            $group->get('/model/{name}/{extension}/{uuid}',"$api_namespace\Orm:read");
        })->add($session_auth);

        Arikaim::$app->get('/core/api/session/',"$api_namespace\Session:getInfo");
        // Install
        Arikaim::$app->post('/core/api/install/',"$api_namespace\Install:install");
        // Install page
        Arikaim::$app->get('/install/',"$controller_namespace\\PageLoader:loadInstallPage");
            
        return true;      
    }

    /**
     * Get route url
     *
     * @param string $pattern
     * @param array $data
     * @param array $query_params
     * @return string
     */
    public static function getRouteUrl($pattern, array $data = [], array $query_params = []): string
    {      
        $segments = [];
        $segment_name = '';
        $parser = new Std();
        $expressions = array_reverse($parser->parse($pattern));

        foreach ($expressions as $expression) {

            foreach ($expression as $segment) {               
                if (is_string($segment)) {
                    $segments[] = $segment;
                    continue;
                }
                if (!array_key_exists($segment[0], $data)) {
                    $segments = [];
                    $segment_name = $segment[0];
                    break;
                }
                $segments[] = $data[$segment[0]];
            }            
            
            if (!empty($segments)) {
                break;
            }
        }

        if (empty($segments) == true) {
            return $pattern;
        }

        $url = implode('', $segments);
        if ($query_params) {
            $url .= '?' . http_build_query($query_params);
        }

        return $url;
    }

     /**
     * Return true if route pattern have placeholder
     *
     * @return boolean
     */
    public static function hasPlaceholder($pattern)
    {
        return preg_match("/\{(.*?)\}/",$pattern);
    }

    public static function findRoute()
    {
        $routes = Arikaim::$app->getRouteCollector()->getRoutes();
        print_r($routes);

    }
}
