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
use Arikaim\Core\Http\Url;

/**
 * Page Api controller
*/
class Page extends ApiController 
{
    /**
     * Load html page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @param stringnull $pageName
     * @return Psr\Http\Message\ResponseInterface
     */
    public function loadPageHtml($request, $response, $data, $pageName = null) 
    {        
        $pageName = (empty($pageName) == true) ? $this->resolvePageName($request,$data) : $pageName;

        $component = $this->get('page')->render($pageName);
        $files = $this->get('view')->properties()->get('include.page.files',[]);

        $result = [
            'html'       => $component->getHtmlCode(),
            'css_files'  => (isset($files['css']) == true) ? $files['css'] : [],
            'js_files'   => (isset($files['js']) == true)  ? $files['js'] : [],
            'properties' => \json_encode($component->getProperties())
        ];

        return $this->setResult($result)->getResponse();       
    }

   /**
     * Load library details 
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data    
     * @return Psr\Http\Message\ResponseInterface
     */
    public function loadLibraryDetails($request, $response, $data) 
    {        
        $libraryName = $data->get('name',null);
        $data = $this->get('page')->getLibraryDetails($libraryName);
        $result = [
            'name'        => $libraryName,
            'css_files'   => (isset($data['files']['css']) == true) ? $data['files']['css'] : [],
            'js_files'    => (isset($data['files']['js']) == true)  ? $data['files']['js'] : [],
            'async'       => $data['async'],
            'crossorigin' => $data['crossorigin']
        ];
       
        return $this->setResult($result)->getResponse();       
    }

    /**
     * Get html page properties
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function loadPageProperties($request, $response, $data)
    {       
        $pageName = $data->get('name',$this->get('page')->getCurrent());    
    
        $result['properties'] = [
            'name'              => $pageName,            
            'library'           => $this->get('page')->getIncludedLibraries(),
            'language'          => $this->getPageLanguage($data),       
            'site_url'          => Url::BASE_URL
        ];

        return $this->setResult($result)->getResponse();       
    }
}
