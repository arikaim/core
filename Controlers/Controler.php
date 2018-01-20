<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Controlers;

use Arikaim\Core\Arikaim;

class Controler
{       
    protected $response;
    protected $request;

    public function __construct() 
    { 
      $this->request  = Arikaim::request();
      $this->response = Arikaim::response();
    }

    public function getParams($request) 
    {
      $params = explode('/', $request->getAttribute('params'));
      $params = array_filter($params);
      $vars = $request->getQueryParams();
      $result = array_merge($params,$vars);    
      return $result;
    }
    
    public static function getControlersNamespace()
    {
      return "\Arikaim\Core\Controlers";
    }
}
