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
use Arikaim\Core\Packages\PackageManager;
use Arikaim\Core\App\ArikaimStore;

/**
 * Repository controller
*/
class Repository extends ControlPanelApiController
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
     * Dowload and install repository from repository
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function repositoryDownloadController($request, $response, $data)
    { 
        $this->onDataValid(function($data) {            
            $type = $data->get('type',null);
            $package = $data->get('package',null);
            $reposioryName = $data->get('repository',null);
            $reposioryType = $data->get('repository_type',PackageManager::GITHUB_REPOSITORY);

            $packageManager = $this->get('packages')->create($type);
            if (\is_object($packageManager) == false) {
                $this->error('Not valid package type.');
                return false;
            }
            $store = new ArikaimStore();
            $accessKey = $store->getPackageKey($reposioryName);

            $repository = $packageManager->getRepository($package,$accessKey);
            if (empty($repository) == true) {               
                $repository = $packageManager->createRepository($reposioryName,$accessKey,$reposioryType);
            }
            if (\is_object($repository) == false) {
                $this->error('Not valid package name or repository.');
                return false;
            }
            if (($repository->isPrivate() == true) && (empty($accessKey) == true)) {
                $this->error('Missing package license key.');
                return false;
            }
            // backup
            if ($type != PackageManager::LIBRARY_PACKAGE) {
                // create package backup
                $packageManager->createBackup($package);
            }

            $result = $repository->install();
            
            $this->setResponse($result,function() use($package,$type) {            
                $this
                    ->message($type . '.download')
                    ->field('type',$type)   
                    ->field('name',$package);                  
            },'errors.' . $type . '.download');
        });
        $data->validate();       
    }
}