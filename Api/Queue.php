<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Api;

use Arikaim\Core\Controllers\ControlPanelApiController;
use Arikaim\Core\Queue\Cron;
use Arikaim\Core\Queue\QueueWorker;
use Arikaim\Core\System\Error\PhpError;

/**
 * Queue controller
*/
class Queue extends ControlPanelApiController
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
     * Start queue worker
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function startWorkerController($request, $response, $data)
    {  
        $this->onDataValid(function($data) {            
            $worker = new QueueWorker($this->get('queue'),$this->get('options'),$this->get('logger'));
            $result = $worker->runDaemon();
            
            $this->setResponse($result,'queue.run','errors.queue.run');
        });
        $data->validate();      
    }
    
    /**
     * Stop queue worker
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function stopWorkerController($request, $response, $data)
    {
        $this->onDataValid(function($data) {              
            $worker = new QueueWorker($this->get('queue'),$this->get('options'),$this->get('logger'));
            $result = $worker->stopDaemon();
            if ($result == false) {
                $error = PhpError::getPosixError();
                $this->error($error);
            } else {
                $this->nessage('queue.stop');
            }
           
        });
        $data->validate();          
    }

    /**
     * Delete jobs
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function deleteJobsController($request, $response, $data)
    { 
        $this->onDataValid(function($data) {                        
        });
        $data->validate();           
    }
    
    /**
     * Install cron scheduler entry
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function installCronController($request, $response, $data)
    {         
        $cron = new Cron();
        $result = ($cron->isInstalled() == false) ? $cron->install() : true;
        
        var_dump($result);
        
        exit();

        $this->setResponse($result,'cron.install','errors.cron.install');              
    }

    /**
     * Uninstall cron scheduler entry
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function unInstallCronController($request, $response, $data)
    {         
        $cron = new Cron();
        $result = $cron->unInstall();

        $this->setResponse($result,'cron.uninstall','errors.cron.uninstall');               
    }
}
