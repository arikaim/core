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
use Arikaim\Core\Form\Form;
use Arikaim\Core\View\TemplatesManager;
use Arikaim\Core\View\Template;
use Arikaim\Core\View\Theme;

class TemplatesApi extends ApiControler
{
    public function setCurrent($request, $response, $args)
    {       
        // access from contorl panel only 
        $this->requireControlPanelPermission();
        
        $this->form->addRule('name',Form::Rule()->templatePath($args['name']));      
        if ($this->form->validate($args) == true) {
            try {
                $current_template = Template::getTemplateName();
                $template_name = $this->form->get('name');  
                $templates = new TemplatesManager();

                // uninstall current template routes
                $result = $templates->uninstall($current_template);
                // install new template routes
                $result = $templates->install($template_name);
                
                if ($result == false) {
                    // roll back current template
                    $templates->install($current_template);
                    $this->setApiError(Arikaim::getError("SYSTEM_ERROR"));
                } else {                
                    $this->setApiResult(['name' => $template_name]);
                }
            } catch(\Exception $e) {
                $this->setApiError($e->getMessage());
            }
        } else {
            $this->setApiErrors($this->form->getErrors());
        }
        return $this->getApiResponse();
    }

    public function setCurrentTheme($request, $response, $args)
    {       
        // access from contorl panel only 
        $this->requireControlPanelPermission();
        
        $this->form->setFields($request->getParsedBody());      

        if ($this->form->validate() == true) {
            $theme_name = $this->form->get('theme_name');
            $template_name = $this->form->get('template_name');
            if (empty($template_name) == true) {
                $template_name = Template::getTemplateName();
            }
            Theme::setCurrentTheme($theme_name,$template_name);
            $this->setApiResult(['theme' => $theme_name]);
            $this->setApiResult(['template' => $template_name]);
        } else {
            $this->setApiError("Not valid theme!");
            $this->setApiResult(['theme' => $this->form->get('theme')]);
        }
        return $this->getApiResponse();
    }
}
