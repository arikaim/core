<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Api\Ui;

use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\Collection\Arrays;
use Arikaim\Core\View\Html\ResourceLocator;

/**
 * Component Api controller
*/
class Component extends ApiController
{
    /**
     * Get html component properties
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function componentProperties($request, $response, $data)
    {
        $language = $this->getPageLanguage($data); 
        $component = $this->get('page')->createHtmlComponent($data['name'],[],$language);

        if (\is_object($component) == false) {
            return $this->withError('TEMPLATE_COMPONENT_NOT_FOUND')->getResponse();  
        }
        
        $component = $component->renderComponent();

        if ($component->hasError() == true) {
            $error = $component->getError();           
            return $this->withError($this->get('errors')->getError($error['code'],$error['params']))->getResponse();  
        }
        // deny requets 
        if ($component->getOption('access/deny-request') == true) {
            return $this->withError($this->get('errors')->getError('ACCESS_DENIED'))->getResponse();           
        }
        
        return $this->setResult($component->getProperties())->getResponse();        
    }

    /**
     * Get html component details
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function componentDetails($request, $response, $data)
    {
        // control panel only
        $this->requireControlPanelPermission();

        $component = $this->get('page')->createHtmlComponent($data['name']);
        if (\is_object($component) == false) {
            return $this->withError('TEMPLATE_COMPONENT_NOT_FOUND')->getResponse();  
        }

        $component = $component->renderComponent();

        if ($component->hasError() == true) {
            $error = $component->getError();
            return $this->withError($this->get('errors')->getError($error['code'],$error['params']))->getResponse();            
        }
        $details = [
                'properties' => $component->getProperties(),
                'options'    => $component->getOptions(),
                'framework'  => $component->getFramework(),
                'files'      => $component->getFiles()
        ];
        
        return $this->setResult($details)->getResponse();       
    }

   /**
     * Load html component
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function loadComponent($request, $response, $data)
    {       
        $params = $this->getParams($request);
     
        // get header params
        $headerParams = $this->getHeaderParams($request);
        $params = \array_merge($params,$headerParams);
        $params = \array_merge($params,$data->toArray());
      
        $language = $this->getPageLanguage($params);
        $this->get('view')->addGlobal('current_language',$language);
      
        return $this->load($data['name'],$params,$language);
    }

    /**
     * Load html component
     *
     * @param string $name
     * @param array $params
     * @param string $language
     * @return JSON 
     */
    public function load($name, $params = [], $language)
    {   
        $framework = (isset($params['framework']) == true) ? $params['framework'] : null;
        if (empty($framework) == true) {
            $template = ResourceLocator::getTemplateName($name,$this->get('view')->getPrimaryTemplate());
            $framework = $this->get('page')->getFramework($template);      
        }
        $component = $this->get('page')->createHtmlComponent($name,$params,$language,true,$framework);
     
        if (\is_object($component) == false) {
            return $this->withError('TEMPLATE_COMPONENT_NOT_FOUND',[
                'full_component_name' => $name
            ])->getResponse();  
        }
     
        $component = $component->renderComponentData($component->getComponentData(),$params);
    
        if ($component->hasError() == true) {
            $error = $component->getError();
            $this->setResultField('redirect',$component->getOption('access/redirect')); 
                      
            return $this->withError($error['message'])->getResponse();          
        }
      
        if ($component->getOption('access/deny-request') == true) {
            $this->setResultField('redirect',$component->getOption('access/redirect')); 

            return $this->withError('ACCESS_DENIED',['name' => $component->getFullName()])->getResponse();           
        }
        $files = $this->get('page')->getComponentsFiles();
        
        $result = [
            'html'       => $component->getHtmlCode(),
            'css_files'  => (isset($files['css']) == true) ? Arrays::arrayColumns($files['css'],['url','params']) : [],
            'js_files'   => (isset($files['js']) == true)  ? Arrays::arrayColumns($files['js'],['url','params'])  : [],
            'properties' => \json_encode($component->getProperties()),
            'framework'  => $component->getFramework(),
            'html'       => $component->getHtmlCode()           
        ];
  
        return $this->setResult($result)->getResponse();        
    }

    /**
     * Get header params
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return array
     */
    private function getHeaderParams($request)
    {       
        $headerParams = (isset($request->getHeader('Params')[0]) == true) ? $request->getHeader('Params')[0] : null;
        
        if ($headerParams != null) {
            $headerParams = \json_decode($headerParams,true);
            if (\is_array($headerParams) == true) {
                return $headerParams;
            }
        }

        return [];
    }
}
