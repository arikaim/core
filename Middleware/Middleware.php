<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Middleware;

use Arikaim\Core\Db\Model;
use Arikaim\Core\Arikaim;

/**
 *  Middleware base class
 */
class Middleware
{
    /**
     * Middleware params
     *
     * @var array
     */
    protected $params;

    /**
     * Constructor
     *
     * @param array $params
     */
    public function __construct($params = [])
    {
        $this->params = $params;
    }
    
    /**
     * Get param value
     *
     * @param string $name
     * @return mixed|null
     */
    public function getParam($name)
    {
        return (isset($this->params[$name]) == true) ? $this->params[$name] : null;
    }

    /**
     * Set param
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;        
    }

    /**
     * Set param
     *
     * @param string $name
     * @param mixed $value
     * @return Middleware
     */
    public function withParam($name, $value)
    {
        $this->setParam($name,$value);
        return $this;
    }

    /**
     * Resolve auth error,  redirect or show error page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface|null $response
     * @return void
     */
    protected function resolveAuthError($request, $response = null)
    {
        $response = ($response == null) ? Arikaim::$app->handle($request) : $response;
        $route = $request->getAttribute('route');  
        if (is_object($route) == true) {
            $pattern = $route->getPattern();
            $routeModel = Model::Routes()->getRoute('GET',$pattern);

            if (is_object($routeModel) == true) {
                $model = Model::Routes()->getAuthErrorRoute($routeModel->extension_name,$routeModel->auth);
                if (is_object($model) == true) {
                    if (empty($model->redirect_url) == false) {                       
                        return $response->withRedirect($model->getRedirectUrl());
                    }                    
                    return Arikaim::page()->load($model->getPageName());
                }
            }         
        }
   
        return Arikaim::errors()->displayRequestError($request,$response,'AUTH_FAILED');
    }
}
