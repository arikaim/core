<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\System;

use Arikaim\Core\Arikaim;
use Arikaim\Core\Utils\Curl;

use Composer\Composer;
use Composer\Factory;
use Composer\Console\Application;
use Composer\IO\NullIO;
use Composer\Repository\ComposerRepository;
use Composer\Repository\PlatformRepository;
use Composer\Repository\CompositeRepository;
use Composer\Repository\RepositoryInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ComposerApplication extends Application
{   
    public function __construct() 
    {
        parent::__construct();
        $base_path = $this->getComposerBinPath();
        putenv('COMPOSER_HOME=' . $base_path);
        $config_file = $this->getComposerRootPath() . DIRECTORY_SEPARATOR . "composer.json";
        putenv('COMPOSER=' . $config_file);
       
        $composer = Factory::create(new NullIO(),$config_file,$this->getComposerRootPath());
        $this->setComposer($composer);
    }

    public function setComposer($composer)
    {
        $this->composer = $composer;
    }

    public function updatePackage($name, $options = [])
    {
        return $this->runCommand('update', [$name] + $options);
    }

    public function updateAllPackages($options = []){
        return $this->runCommand('update', $options);
    }

    public function searchPackage($search)
    {
        $composer = $this->getComposer(true, false);
        $platform_repo = new PlatformRepository();
        $local_repo = $composer->getRepositoryManager()->getLocalRepository();
        $installed_repo = new CompositeRepository(array($local_repo, $platform_repo));

        $repos = new CompositeRepository(array_merge(array($installed_repo), $composer->getRepositoryManager()->getRepositories()));
        $flags = RepositoryInterface::SEARCH_FULLTEXT;
        $results = $repos->search($search, $flags);
        return $results;
    }

    public function findPackage($name, $version = null)
    {
        $repositoryManager = $this->getComposer()->getRepositoryManager();
        $package = $repositoryManager->findPackage($name, $version);
        return $package;
    }

    public function executeCommand($cmd = 'update')
    {
        $base_path = $this->getComposerBinPath();
        putenv('COMPOSER_HOME=' . $base_path);

        // call `composer install` command programmatically
        $input = new ArrayInput(array('command' => $cmd));
        $application = new Application();
        $application->setAutoExit(false); 
        $output = new BufferedOutput();
        $application->run($input,$output);
        return $output;
    }

    public function runCommand($command, $params = [])
    {
        $parameters = array_merge(['command' => $command],$params);            

        $input = new ArrayInput($parameters);
        $output = new BufferedOutput();
        try {
            $this->run($input, $output);
        }catch (\Exception $c){
            $output->write($c->getMessage());
        }
        return $output->fetch();
    }

    public function getComposerRootPath()
    {
        return ARIKAIM_ROOT_PATH . ARIKAIM_BASE_PATH;
    }

    public function getComposerBinPath()
    {
        return $this->getComposerRootPath() . '/vendor/bin/composer';
    }

    public function getLocalPackages()
    {
        return $this->getComposer()->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
    }
}