<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\Controllers;

use Psr\Http\Message\ResponseInterface;

use Arikaim\Core\Arikaim;
use Arikaim\Core\System\Url;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Access\Access;
use Arikaim\Core\Collection\Arrays;
use Arikaim\Core\View\Template\Template;
use Arikaim\Core\Validator\Validator;
use Arikaim\Core\View\Html\HtmlComponent;

/**
 * Base class for all Controllers
*/
class Controller
{
    const PAGE = 1;
    const API  = 2;

    /**
     * Extension name
     *
     * @var string|null
     */
    protected $extensionName;   

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
        $this->extensionName = Arikaim::getContainer()->getItem('contoller.extension');
        $this->type = Controller::PAGE;
        $this->messages = [];
        $this->init();
    }

    /**
     * Get extension name
     *
     * @return string|null
     */
    public function getExtensionName()
    {
        return $this->extensionName;
    }

    /**
     * Set extension name
     *
     * @param string $name
     * @return void
     */
    public function setExtensionName($name)
    {
        $this->extensionName = $name;
    }
    
    /**
     * Add system error
     *
     * @param string $name
     * @return boolean
    */
    public function addError($name)
    {
        $message = $this->getMessage($name);
        $message = (empty($message) == true) ? $name : $message;
        
        return Arikaim::errors()->addError($message);
    }

    /**
     * Get url
     *
     * @param ServerRequestInterface $request 
     * @param boolean $relative
     * @return string
     */
    public function getUrl($request, $relative = false)
    {
        $path = $request->getUri()->getPath();
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
     * Load messages from html component json file
     *
     * @param string $componentName
     * @param string $language
     * @return void
     */
    public function loadMessages($componentName, $language = null)
    {
        $messages = HtmlComponent::getProperties($componentName,$language);
        $this->messages = (is_object($messages) == true) ? $messages->toArray() : [];
    }

    /**
     * Get message
     *
     * @param string $name
     * @return string
     */
    public function getMessage($name)
    {
        return (isset($this->messages[$name]) == true) ? $this->messages[$name] : Arrays::getValue($this->messages,$name,'.');        
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
                Arikaim::$app->respond($this->getResponse()); 
                Arikaim::end();       
            }   
            default: {
                $response = Arikaim::page()->loadSystemError();
                Arikaim::$app->respond($response); 
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
     * @param string|null $name Page name  
     * @return Psr\Http\Message\ResponseInterface
    */
    public function loadPage($request, $response, $data, $pageName = null)
    {       
        $language = $this->getPageLanguage($data);
        if (empty($pageName) == true) {
            $pageName = (isset($data['page_name']) == true) ? $data['page_name'] : $this->resolvePageName($request,$data);
        } 
      
        $data = (is_object($data) == true) ? $data->toArray() : $data;
        if (empty($pageName) == true) {
            return Arikaim::page()->loadPageNotFound($data,$language);    
        } 
        
        return Arikaim::page()->load($pageName,$data,$language,$response);
    }

    /**
     * Resolve page name
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param Validator $data    
     * @return string|null
     */
    protected function resolvePageName($request, $data)
    {            
        // try from reutes db table
        $route = $request->getAttribute('route');  
        $pageName = null;
        if (is_object($route) == true) {
            $pattern = $route->getPattern();          
            $model = Model::Routes()->getRoute('GET',$pattern);            
            $pageName = (is_object($model) == false) ? null : $model->getPageName();             
        } 

        return $pageName;
    }

    /**
     * Display page not found
     *    
     * @param array $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function pageNotFound($data = [])
    {     
        $language = $this->getPageLanguage($data);

        return Arikaim::page()->loadPageNotFound($data,$language,$this->getExtensionName());    
    }

    /**
     * Display system error page
     *    
     * @param array $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function systemErrorPage($data = [])
    {     
        $language = $this->getPageLanguage($data);

        return Arikaim::page()->loadSystemError($data,$language,$this->getExtensionName());    
    }

    /**
     * Write XML to reponse body
     *
     * @param ResponseInterface $response
     * @param string $xml
     * @return ResponseInterface
     */
    public function writeXml(ResponseInterface $response, $xml)
    {
        $response->getBody()->write($xml);
        return $response->withHeader('Content-Type','text/xml');
    }
}
