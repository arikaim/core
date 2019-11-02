<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Core\Packages\Template;

use Arikaim\Core\Packages\PackageRepository;
use Arikaim\Core\Interfaces\Packages\RepositoryInterface;
use Arikaim\Core\System\Path;
use Arikaim\Core\Arikaim;
use Arikaim\Core\FileSystem\File;

/**
 * Template package repository
*/
class TemplateRepository extends PackageRepository implements RepositoryInterface
{
    /**
     * Constructor
     *
     * @param string $repositoryUrl
     */
    public function __construct($repositoryUrl)
    {
        parent::__construct($repositoryUrl);
    }

    /**
     * Install package
     *
     * @param string|null $version
     * @return boolean
     */
    public function install($version = null)
    {
        $version = (empty($version) == true) ? $this->getRepositoryDriver()->getLastVersion() : $version;
        $result = $this->getRepositoryDriver()->download($version);

        if ($result == true) {
            $repositoryFolder = $this->extractRepository($version);
            if ($repositoryFolder == false) {
                // Error extracting zip repository file
                return false;
            }
            $json = Arikaim::storage()->read('temp/' . $repositoryFolder . '/template.json');
            if ($json != false) {
                $templateProperties = json_decode($json,true);
                $templateName = (isset($templateProperties['name']) == true) ? $templateProperties['name'] : false;
                if ($templateName != false) {   
                    $sourcePath = Path::STORAGE_TEMP_PATH . $repositoryFolder;
                    $destinatinPath = Path::getTemplatePath($templateName);
                    $result = File::copy($sourcePath,$destinatinPath);
                    
                    return $result;
                }
                // Missing package name in template.json file.
                return false;
            }
            // Not valid package
            return false;
        }

        // Can't download repository
        return false;
    }
}