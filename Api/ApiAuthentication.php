<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Api;

use Arikaim\Core\Arikaim;

class ApiAuthentication 
{
    public function __construct()
    {
    }

    public function showError($request, $response) 
    {   
        $response = new RestApiResponse($response);
        $response->setError(Arikaim::getError("AUTH_FAILED"));
        return $response->getResponse();
    }
}
