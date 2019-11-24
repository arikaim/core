<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\App;

use FastRoute\RouteParser\Std;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Traits\Db\Status;
use Arikaim\Core\Arikaim;
use Arikaim\Core\App\Path;

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
        $routes = Arikaim::cache()->fetch('routes.list');
        if (is_array($routes) == false) {
            if (Arikaim::db()->isValidPdoConnection() == false) {
                return false;
            }
    
            if (Arikaim::errors()->hasError() == true) {
                return false;
            }

            $routes = Model::Routes()->getRoutes(Status::$ACTIVE);  
            Arikaim::cache()->save('routes.list',$routes,4);         
        }

        foreach($routes as $item) {
            // controller params
            Arikaim::getContainer()['contoller.extension']= $item['extension_name'];

            $methods = explode(',',$item['method']);
            $handler = $item['handler_class'] . ":" . $item['handler_method'];   
            $route = Arikaim::$app->map($methods,$item['pattern'],$handler);
            // auth middleware
            if ($item['auth'] > 0) {
                $middleware = Arikaim::access()->middleware($item['auth']);    
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
        $apiNamespace = Path::API_CONTROLLERS_NAMESPACE;
        $sessionAuth = Arikaim::access()->middleware('session');

        // Control Panel
        Arikaim::$app->get('/admin/[{language:[a-z]{2}}/]',Path::CONTROLLERS_NAMESPACE . "\PageLoader:loadControlPanel");    
        // Api Access
        Arikaim::$app->post('/core/api/create/token/',"$apiNamespace\Client:createToken");
        Arikaim::$app->post('/core/api/verify/request/',"$apiNamespace\Client:verifyRequest");      
        // UI Component
        Arikaim::$app->get('/core/api/ui/component/properties/{name}[/{params:.*}]',"$apiNamespace\Ui\Component:componentProperties");

        Arikaim::$app->get('/core/api/ui/component/details/{name}[/{params:.*}]',"$apiNamespace\Ui\Component:componentDetails")->add($sessionAuth);
        Arikaim::$app->get('/core/api/ui/component/{name}[/{params:.*}]',"$apiNamespace\Ui\Component:loadComponent");      
        Arikaim::$app->post('/core/api/ui/library/upload',"$apiNamespace\Ui\Library:upload")->add($sessionAuth);

        // UI Page  
        Arikaim::$app->get('/core/api/ui/page/{name}',"$apiNamespace\Ui\Page:loadPage");
        Arikaim::$app->get('/core/api/ui/page/properties/',"$apiNamespace\Ui\Page:loadPageProperties");  
        // Paginator 
        Arikaim::$app->group('/core/api/ui/paginator',function($group) use($apiNamespace) {  
            $group->put('/page-size',"$apiNamespace\Ui\Paginator:setPageSize");
            $group->put('/page',"$apiNamespace\Ui\Paginator:setPage");
            $group->get('/[{namespace}]',"$apiNamespace\Ui\Paginator:getPage");
            $group->put('/view/type',"$apiNamespace\Ui\Paginator:setViewType");
            $group->get('/view/type/[{namespace}]',"$apiNamespace\Ui\Paginator:getViewType");
            $group->delete('/{namespace}',"$apiNamespace\Ui\Paginator:remove");
        });     
        // Search 
        Arikaim::$app->group('/core/api/ui/search',function($group) use($apiNamespace) { 
            $group->put('/',"$apiNamespace\Ui\Search:setSearch"); 
            $group->put('/condition/[{namespace}]',"$apiNamespace\Ui\Search:setSearchCondition");      
            $group->delete('/condition/{field}/[{namespace}]',"$apiNamespace\Ui\Search:deleteSearchCondition");
            $group->delete('/[{namespace}]',"$apiNamespace\Ui\Search:clearSearch");
        });
        // Order by column
        Arikaim::$app->group('/core/api/ui/order',function($group) use($apiNamespace) { 
            $group->put('/[{namespace}]',"$apiNamespace\Ui\OrderBy:setOrderBy"); 
            $group->get('/[{namespace}]',"$apiNamespace\Ui\OrderBy:getOrderBy");      
            $group->delete('/[{namespace}]',"$apiNamespace\Ui\OrderBy:deleteOrderBy");
        })->add($sessionAuth);        
        // Position
        Arikaim::$app->group('/core/api/ui/position',function($group) use($apiNamespace) { 
            $group->put('/shift',"$apiNamespace\Ui\Position:shift");
            $group->put('/swap',"$apiNamespace\Ui\Position:swap");
        })->add($sessionAuth);              
        // Control Panel user
        Arikaim::$app->group('/core/api/user',function($group) use($apiNamespace) {  
            $group->post('/login/',"$apiNamespace\Users:adminLogin");
            $group->post('/password/recovery/',"$apiNamespace\Users:passwordRecovery");
            $group->post('/password/change/',"$apiNamespace\Users:changePassword");
            $group->get('/logout/',"$apiNamespace\Users:logout");
        });
        // Change password page
        Arikaim::$app->get('/admin/change-password/{code}/[{language}/]',Path::CONTROLLERS_NAMESPACE. "\PageLoader:loadChangePassword");
        Arikaim::$app->post('/core/api/user/',"$apiNamespace\Users:changeDetails")->add($sessionAuth);
        // Languages  
        Arikaim::$app->group('/core/api/language',function($group) use($apiNamespace) {      
            $group->post('/add',"$apiNamespace\Language:add");
            $group->put('/update',"$apiNamespace\Language:update");
            $group->delete('/{uuid}',"$apiNamespace\Language:remove");
            $group->put('/change/{language_code}',"$apiNamespace\Language:changeLanguage"); 
            $group->put('/status',"$apiNamespace\Language:setStatus");
            $group->put('/default',"$apiNamespace\Language:setDefault");
        })->add($sessionAuth);        
        // Options
        Arikaim::$app->group('/core/api/options',function($group) use($apiNamespace) {
            $group->get('/{key}',"$apiNamespace\Options:get");
            $group->put('/',"$apiNamespace\Options:save");
            $group->post('/',"$apiNamespace\Options:saveOptions");
        })->add($sessionAuth);
        // Queue
        Arikaim::$app->group('/core/api/queue',function($group) use($apiNamespace) {
            $group->put('/cron/install',"$apiNamespace\Queue:installCron");
            $group->delete('/cron/uninstall',"$apiNamespace\Queue:unInstallCron");
            $group->delete('/jobs',"$apiNamespace\Queue:deleteJobs");
            $group->put('/worker/start',"$apiNamespace\Queue:startWorker");
            $group->delete('/worker/stop',"$apiNamespace\Queue:stopWorker");
        })->add($sessionAuth);
        // Jobs
        Arikaim::$app->group('/core/api/jobs',function($group) use($apiNamespace) {
            $group->delete('/delete/{uuid}',"$apiNamespace\Jobs:deleteJob");
            $group->put('/status',"$apiNamespace\Jobs:setStatus");          
        })->add($sessionAuth);        
        // Drivers
        Arikaim::$app->group('/core/api/driver',function($group) use($apiNamespace) { 
            $group->put('/status',"$apiNamespace\Drivers:setStatus");          
            $group->get('/config/{name}',"$apiNamespace\Drivers:readConfig");
            $group->put('/config',"$apiNamespace\Drivers:saveConfig");
        })->add($sessionAuth);
        // Update
        Arikaim::$app->group('/core/api/update',function($group) use($apiNamespace) {
            $group->get('/',"$apiNamespace\Update:update");
            $group->get('/check',"$apiNamespace\Update:checkVersion"); 
        })->add($sessionAuth);
        // Session
        Arikaim::$app->group('/core/api/session',function($group) use($apiNamespace) {        
            $group->put('/recreate',"$apiNamespace\SessionApi:recreate");
            $group->put('/restart',"$apiNamespace\SessionApi:restart");
        })->add($sessionAuth);
        // Access tokens
        Arikaim::$app->group('/core/api/tokens',function($group) use($apiNamespace) {
            $group->delete('/delete/{token}',"$apiNamespace\AccessTokens:delete");
            $group->delete('/delete/expired/{uuid}',"$apiNamespace\AccessTokens:deleteExpired");
        })->add($sessionAuth);
        // Settings
        Arikaim::$app->group('/core/api/settings',function($group) use($apiNamespace) {
            $group->put('/debug',"$apiNamespace\Settings:setDebug");
        })->add($sessionAuth);
        // Mailer
        Arikaim::$app->group('/core/api/mailer',function($group) use($apiNamespace) {
            $group->get('/test/email',"$apiNamespace\Mailer:sendTestEmail");
        })->add($sessionAuth);
        // Cache
        Arikaim::$app->group('/core/api/cache',function($group) use($apiNamespace) {
            $group->delete('/clear',"$apiNamespace\Cache:clear");
            $group->put('/enable',"$apiNamespace\Cache:enable");
            $group->put('/disable',"$apiNamespace\Cache:disable");
        })->add($sessionAuth);
        // Logs
        Arikaim::$app->group('/core/api/logs',function($group) use($apiNamespace) {
            $group->delete('/clear',"$apiNamespace\Logger:clear");
        })->add($sessionAuth);
        // Orm
        Arikaim::$app->group('/core/api/orm',function($group) use($apiNamespace) {
            $group->put('/relation/delete',"$apiNamespace\Orm:deleteRelation");
            $group->post('/relation',"$apiNamespace\Orm:addRelation");
            $group->put('/options',"$apiNamespace\Orm:saveOptions");
            $group->get('/model/{name}/{extension}/{uuid}',"$apiNamespace\Orm:read");
        })->add($sessionAuth);
        // Packages
        Arikaim::$app->group('/core/api/packages',function($group) use($apiNamespace) {
            $group->put('/install',"$apiNamespace\Packages:install");    
            $group->put('/repository/install',"$apiNamespace\Packages:repositoryInstall");         
            $group->put('/status',"$apiNamespace\Packages:setStatus");
            $group->put('/uninstall',"$apiNamespace\Packages:unInstall");
            $group->put('/update',"$apiNamespace\Packages:update");
            $group->post('/config',"$apiNamespace\Packages:saveConfig");
            $group->put('/current',"$apiNamespace\Packages:setCurrent");
            $group->put('/theme/current',"$apiNamespace\Packages:setCurrentTheme");
        })->add($sessionAuth);
        // Session
        Arikaim::$app->get('/core/api/session/',"$apiNamespace\Session:getInfo");
        // Install
        Arikaim::$app->post('/core/api/install/',"$apiNamespace\Install:install");
        // Install page
        Arikaim::$app->get('/install/',Path::CONTROLLERS_NAMESPACE . "\PageLoader:loadInstallPage");
                   
        return true;      
    }

    /**
     * Get route url
     *
     * @param string $pattern
     * @param array $data
     * @param array $queryParams
     * @return string
     */
    public static function getRouteUrl($pattern, array $data = [], array $queryParams = []): string
    {      
        $segments = [];
        $segmentName = '';
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
                    $segmentName = $segment[0];
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
        if ($queryParams) {
            $url .= '?' . http_build_query($queryParams);
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
}
