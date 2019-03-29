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
use Arikaim\Core\System\Url;

/**
 * Site stats middleware class
 */
class SiteStats 
{
    /**
     * Add record to site stats 
     *
     * @param object $request
     * @param object $response
     * @param object $next
     * @return object
     */
    public function __invoke($request, $response, $next) 
    {   
        $uri = $request->getUri();
        $path = $uri->getPath();
        
        $base_url = (substr(Url::ARIKAIM_BASE_URL,-1) == "/") ? substr(Url::ARIKAIM_BASE_URL,0,-1) : Url::ARIKAIM_BASE_URL;

        $log = [
            'method'    => $request->getMethod(),
            'path'      => $path,
            'domain'    => ARIKAIM_DOMAIN,
            'base_url'  => $base_url,
            'http_user_agent' => $request->getheader('HTTP_USER_AGENT'),
            'client_ip' => $request->getAttribute('client_ip'),
            'url'       => $base_url. "/" . $path
        ];
 
        if (Arikaim::errors()->hasError() == false) {            
            Arikaim::stats()->addStats('Request',$log);
        }
        $response = $next($request, $response);
        return $response;        
    }
}
