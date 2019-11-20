<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\System\Error;

use Arikaim\Core\Controllers\Response;
use Arikaim\Core\Interfaces\ErrorRendererInterface;

/**
 * Render error
 */
class JsonErrorRenderer implements ErrorRendererInterface
{
    /**
     * Constructor
     *
     */
    public function __construct()
    {
    }

    /**
    * Render error
    *
    * @param array $errorDetails
    * 
    * @return void
    */
   public function render($errorDetails)
   {
       $response = new Response();
       $response->setError($errorDetails['message']);
      
       echo $response->getResponseJson();
   }
}
