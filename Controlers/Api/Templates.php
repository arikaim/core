<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Controlers\Api;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Controlers\ApiControler;
use Arikaim\Core\Packages\Template\TemplatesManager;
use Arikaim\Core\View\Template;
use Arikaim\Core\View\Theme;

/**
 * Control panel templates controler
*/
class Templates extends ApiControler
{
     /**
     * Set current template
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function setCurrent($request, $response, $data)
    {       
        // access from contorl panel only 
        $this->requireControlPanelPermission();
    
        $data->addRule('name',$data->rule()->templatePath($data['name']));      

        if ($data->validate() == true) {
            try {
                $current_template = Template::getTemplateName();
                $templates = new TemplatesManager();

                // uninstall current template routes 
                $result = $templates->unInstallPackage($current_template);
              
                // install new template routes
                $result = $templates->installPackage($data['name']);
                if ($result == false) {
                    // roll back current template
                    $templates->installPackage($current_template);
                    $this->setApiError(Arikaim::getError("SYSTEM_ERROR"));
                } else {                
                    $this->setApiResult(['name' => $data['name']]);
                }
            } catch(\Exception $e) {
                $this->setApiError($e->getMessage());
            }
        } else {
            $this->setApiErrors($data->getErrors());
        }
        return $this->getApiResponse();
    }

    public function install($request, $response, $data)
    { 
        // access from contorl panel only 
        $this->requireControlPanelPermission();
        
        $data->addRule('name',$data->rule()->templatePath($data['name']));      
        $templates = new TemplatesManager();

        if ($data->validate() == true) {
            $result = $templates->installPackage($data['name']);
            if ($result == false) {
                $this->setApiError(Arikaim::getError("SYSTEM_ERROR"));
            } else {
                $this->setApiResult(['name' => $data['name']]);
            }
        } else {
            $this->setApiErrors($data->getErrors());
        }

        return $this->getApiResponse();
    }

    /**
     * Set current theme
     *
     * @param object $request
     * @param object $response
     * @param Validator $data
     * @return object
    */
    public function setCurrentTheme($request, $response, $data)
    {       
        // access from contorl panel only 
        $this->requireControlPanelPermission();
      
        if ($data->validate() == true) {
            $theme_name = $data->get('theme_name');
            $template_name = $data->get('template_name');
            if (empty($template_name) == true) {
                $template_name = Template::getTemplateName();
            }
            Theme::setCurrentTheme($theme_name,$template_name);
            $this->setApiResult(['theme' => $theme_name]);
            $this->setApiResult(['template' => $template_name]);
        } else {
            $this->setApiError("Not valid theme!");
            $this->setApiResult(['theme' => $data->get('theme')]);
        }
        return $this->getApiResponse();
    }
}
