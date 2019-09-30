<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Controllers\Api\Ui;

use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\Db\Model;

/**
 * Position Api controller
*/
class Position extends ApiController 
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
     * Set paginator current page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function shift($request, $response, $data) 
    {       
        $this->requireControlPanelPermission();

        $model = $this->createModel($data);

        if (is_object($model) == true) {          
            $target_model = $model->findById($data->get('target_uuid'));
            if (is_object($target_model) == true) {                    
                $model->shiftPosition($target_model);
            }
            $this->message('done');
        } else {
            $this->error("errors.position");
        }
       
        return $this->getResponse();
    }

    /**
     * Create model object form request data
     *
     * @param Validator $data
     * @return Model|false
     */
    public function createModel($data)
    {
        $model = Model::create($data->get('model_name'));
        return (is_object($model) == true) ? $model->findById($data->get('uuid')) : false;      
    }

    /**
     * Set paginator current page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function swap($request, $response, $data) 
    {
        $this->requireControlPanelPermission();

        $model = $this->createModel($data);

        if (is_object($model) == true) {          
            $target_model = $model->findById($data->get('target_uuid'));
            if (is_object($target_model) == true) {                    
                $model->swapPosition($target_model);
            }
            $this->message('done');
        } else {
            $this->error("errors.position");
        }
       
        return $this->getResponse();
    }
}
