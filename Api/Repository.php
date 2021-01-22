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
use Arikaim\Core\Db\Model;
use Arikaim\Core\System\Composer;
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

            $packageManager = $this->get('packages')->create($type);
            if (\is_object($packageManager) == false) {
                $this->error('Not valid package type.');
                return false;
            }
            $store = new ArikaimStore();
            $accessKey = $store->getPackageKey($package);

            $repository = $packageManager->getRepository($package,$accessKey);
            if (empty($repository) == true) {
                $repository = $packageManager->createRepository($reposioryName,$accessKey);
            }
            if (\is_object($repository) == false) {
                $this->error('Not valid package name or repository.');
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

    /**
     * Dowload and install package from repository
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function repositoryInstallController($request, $response, $data)
    { 
        $this->onDataValid(function($data) {  
            $this->get('cache')->clear();
            $type = $data->get('type',null);
            $package = $data->get('package',null);
            $reposioryType = $data->get('repository_type',null);

            $packageManager = $this->get('packages')->create($type);
            $repositoryUrl = PackageManager::createRepositoryUrl($package,$reposioryType);
            $accessKey = null;
            if ($reposioryType == 'arikaim') {
                $store = new ArikaimStore();
                $accessKey = $store->getPackageKey($package);
            }
            $repository = $packageManager->createRepository($repositoryUrl,$accessKey);

            $this->get('cache')->clear();

            $result = (\is_object($repository) == true) ? $repository->install() : false;

            $this->setResponse($result,function() use($package,$type) {            
                $this
                    ->message($type . '.download')
                    ->field('type',$type)   
                    ->field('name',$package);                  
            },'errors.' . $type . '.download');

        });
        $data->validate();       
    }

    /**
     * Dowload and update package from repository
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function repositoryUpdateController($request, $response, $data)
    {
        $this->onDataValid(function($data) {  
            $this->get('cache')->clear();

            $type = $data->get('type',null);
            $name = $data->get('package',null);
            $packageManager = $this->get('packages')->create($type);

            if ($type != PackageManager::LIBRARY_PACKAGE) {
                // create package backup
                $packageManager->createBackup($name);
            }
        
            $repository = $packageManager->getRepository($name);
            $result = (\is_object($repository) == true) ? $repository->install() : false;
            
            $package = $packageManager->createPackage($name);
            $version = (\is_object($package) == true) ? $package->getVersion() : null;

            $this->get('cache')->clear();
            
            $this->setResponse($result,function() use($name,$type,$version) {            
                $this
                    ->message($type . '.download')
                    ->field('type',$type) 
                    ->field('type',$version)   
                    ->field('name',$name);                  
            },'errors.' . $type . '.download');
        });
        $data->validate();       
    }
}
