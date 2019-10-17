<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Controllers;

use Arikaim\Core\Arikaim;
use Arikaim\Core\System\Url;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Access\Access;
use Arikaim\Core\Utils\Arrays;
use Arikaim\Core\View\Template\Template;
use Arikaim\Core\Validator\Validator;
use HtmlComponent;

/**
 * Base class for all Controllers
*/
class Controller
{
    const PAGE = 1;
    const API  = 2;

    /**
     * Controller type API or PAGE
     *
     * @var integer
     */
    protected $type;

    /**
     * Response messages
     *
     * @var array
     */
    protected $messages;

    /**
     * Constructor
     */
    public function __construct()
    { 
        $this->type = Controller::PAGE;
        $this->messages = [];
        $this->init();
    }

    /**
     * Get url
     *
     * @param boolean $relative
     * @return string
     */
    public function getUrl($relative = false)
    {
        $path = Arikaim::request()->getUri()->getPath();
        return ($relative == true ) ? $path : Url::ARIKAIM_BASE_URL . '/' . $path;
    }

    /**
     * Init controller, override this method in child classes
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Load respons emessages from html component json file
     *
     * @param string $component_name
     * @param string $language
     * @return void
     */
    public function loadMessages($component_name, $language = null)
    {
        $messages = HtmlComponent::getProperties($component_name,$language);
        $this->messages = (is_object($messages) == true) ? $messages->toArray() : [];
    }

    public function getMessage($name)
    {
        if (isset($this->messages[$name]) == true) {
            return $this->messages[$name];
        }
        return Arrays::getValue($this->messages,$name,'.');
    }

    /**
     * Return current logged user
     *
     * @return mixed
     */
    public function user()
    {
        return Arikaim::auth()->getUser();
    }

    /**
     * Set callback for validation errors
     *
     * @param \Closure $callback
     * @return void
    */
    public function onValidationError(\Closure $callback)
    {
        $function = function($event) use(&$callback) {
            return $callback($event->toArray());
        };
        Arikaim::event()->subscribeCallback('validator.error',$function,true);
    }
    
    /**
     * Set callback for validation done
     *
     * @param \Closure $callback
     * @return void
     */
    public function onDataValid(\Closure $callback)
    {
        $function = function($event) use(&$callback) {
            return $callback($event->toCollection());
        };
        Arikaim::event()->subscribeCallback('validator.valid',$function,true);
    }

    /**
     * Get request params
     *
     * @param object $request
     * @return array
     */
    public function getParams($request)
    {
        $params = explode('/', $request->getAttribute('params'));
        $params = array_filter($params);
        $vars = $request->getQueryParams();
        return array_merge($params, $vars);       
    }

    /**
     * Require control panel permission
     *
     * @return void
     */
    public function requireControlPanelPermission()
    {
        return $this->requireAccess(Access::CONTROL_PANEL,Access::FULL);
    }
    
    /**
     * Reguire permission check if current user have permission
     *
     * @param string $name
     * @param mixed $type
     * @return bool
     */
    public function requireAccess($name, $type = null)
    {
        $result = Arikaim::access()->hasAccess($name,$type);  
        if ($result == true) {
            return true;
        }
        
        // access denied response
        switch ($this->type) {
            case Controller::API: {    
                $this->setError(Arikaim::getError("AUTH_FAILED"));                        
                Arikaim::getApp()->respond($this->getResponse()); 
                Arikaim::end();       
            }   
            default: {
                $response = Arikaim::page()->load("system:system-error");
                Arikaim::getApp()->respond($response); 
                Arikaim::end(); 
            }         
        }
        return false;
    }

    /**
     * Get page language
     *
     * @param array $data
     * @return string
    */
    public function getPageLanguage($data)
    {
        if (isset($data['language']) == true) {
            $language = $data['language'];
            if (Model::Language()->has($language,true) == false) {
                return false;
            }
            Template::setLanguage($language);
        }     
        return Template::getLanguage();
    }

    /**
     * Load page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data     
     * @return Psr\Http\Message\ResponseInterface
    */
    public function loadPage($request, $response, $data)
    {       
        $language = $this->getPageLanguage($data);
        $page_name = (isset($data['page_name']) == true) ? $data['page_name'] : $this->resolvePageName($request, $data);

        $data = (is_object($data) == true) ? $data->toArray() : $data;
    
        return Arikaim::page()->load($page_name,$data,$language);
    }

    /**
     * Resolve page name
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param Validator $data    
     * @return string
     */
    protected function resolvePageName($request, $data)
    {            
        // try from reutes db table
        $route = $request->getAttribute('route');  
    
        if (is_object($route) == true) {
            $pattern = $route->getPattern();          
            $model = Model::Routes()->getRoute('GET',$pattern);            
            $page_name = (is_object($model) == false) ? 'system:page-not-found' : $model->getPageName();             
        } else {
            $page_name = "system:system-error";
        }   

        return $page_name;
    }

    /**
     * Load page not found
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function pageNotFound($request, $response, $data = [])
    {     
        $language = $this->getPageLanguage($data);

        return Arikaim::page()->load('system:page-not-found',$data,$language);    
    }
}
