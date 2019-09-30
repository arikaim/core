<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Controllers\Api;

use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\Packages\Template\TemplatesManager;
use Arikaim\Core\View\Template\Template;
use Arikaim\Core\View\Theme;

/**
 * Templates controller
*/
class Templates extends ApiController
{
    /**
     * Init controller
     *
     * @return void
     */
    public function init()
    {
        $this->loadMessages('system:admin.messages');
    }

    /**
     * Set current template
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function setCurrentController($request, $response, $data)
    {       
        // access from contorl panel only 
        $this->requireControlPanelPermission();
        
        $this->onDataValid(function($data) { 
            $current_template = Template::getTemplateName();
            $templates = new TemplatesManager();

            // uninstall current template routes 
            $result = $templates->unInstallPackage($current_template);
            // install new template routes
            $result = $templates->installPackage($data['name']);
            if ($result == false) {
                // roll back current template
                $templates->installPackage($current_template);
                $this->error('errors.template.current');
            } else {                
                $this
                    ->message('template.current')
                    ->field('name',$data['name']);
            }
            
        });
        $data->validate();            
    }

    /**
     * Update template
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function updateController($request, $response, $data)
    { 
        // access from contorl panel only 
        $this->requireControlPanelPermission();
        
        $this->onDataValid(function($data) {
            $templates = new TemplatesManager();
            $result = $templates->installPackage($data['name']);

            $this->setResponse($result,'template.update','errors.template.update');
        });
        $data->validate();      
    }

    /**
     * Set current theme
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function setCurrentThemeController($request, $response, $data)
    {       
        // access from contorl panel only 
        $this->requireControlPanelPermission();
            
        $this->onDataValid(function($data) {
            $theme_name = $data->get('theme_name');
            $template_name = $data->get('template_name',Template::getTemplateName());          
            Theme::setCurrentTheme($theme_name,$template_name);
         
            $this
                ->message('theme.current')
                ->field('theme',$theme_name)
                ->field('template',$template_name);
        });
        $data->validate();
    }
}
