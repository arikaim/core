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
use Arikaim\Core\Arikaim;
use Arikaim\Core\Queue\Cron;
use Arikaim\Core\Queue\QueueWorker;

/**
 * Queue controller
*/
class Queue extends ApiController
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
        $this->requireControlPanelPermission();
        
        $this->onDataValid(function($data) {            
            $worker = new QueueWorker();
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
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) {              
            $worker = new QueueWorker();
            $result = $worker->stopDaemon();

            $this->setResponse($result,'queue.stop','errors.queue.stop');
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
        $this->requireControlPanelPermission();
        
        $this->onDataValid(function($data) {             
              //  Arikaim::jobs()->getQueueService()->removeAllJobs();
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
    public function installCron($request, $response, $data)
    {
        $this->requireControlPanelPermission();
               
        $cron = new Cron();
        $result = ($cron->isInstalled() == false) ? $cron->install() : true;
           
        $this->setResponse($result,'cron.install','errors.cron.install');       
        return $this->getResponse();
    }

    /**
     * Uninstall cron scheduler entry
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function unInstallCron($request, $response, $data)
    {
        $this->requireControlPanelPermission();
               
        $cron = new Cron();
        $result = $cron->unInstall();

        $this->setResponse($result,'cron.uninstall','errors.cron.uninstall');       
        return $this->getResponse();
    }
}
