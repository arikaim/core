<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Middleware;

use Arikaim\Core\Arikaim;

class SiteStats 
{
    public function __construct() 
    {        
    }

    public function __invoke($request, $response, $next) 
    {   
        $uri = $request->getUri();
        $path = $uri->getPath();
          
        $info['method'] = $request->getMethod();
        $info['path'] = $path;
        $info['domain'] = Arikaim::getDomain();
        $info['base_url'] = Arikaim::getBaseUrl();
        $info['url'] = $info['base_url'] . "/" . $path;
        $info['http_user_agent'] = $request->getheader('HTTP_USER_AGENT');
        $info['client_ip'] = $request->getAttribute('client_ip');
    
        if (Arikaim::errors()->hasError("DB_CONNECTION_ERROR") == false) {            
            Arikaim::logger()->addStats('Request',$info);
        }
        $response = $next($request, $response);
        return $response;        
    }
}
