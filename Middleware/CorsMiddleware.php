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

use Arikaim\Core\Form\Form;
use Arikaim\Core\Middleware\ClientIp;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;

class CorsMiddleware
{
    public function __construct()
    {        
    }

    public function __invoke($request, $response, $next)
    {
        $response = $next($request, $response);
        return $response->withHeader('Access-Control-Allow-Credentials',true)
            ->withHeader('Access-Control-Allow-Origin','*')
            ->withHeader('Access-Control-Allow-Methods','POST, GET, OPTIONS, PUT, DELETE');
    }
}
