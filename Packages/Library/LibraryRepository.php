<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Packages\Library;

use Arikaim\Core\Packages\PackageRepository;
use Arikaim\Core\Interfaces\Packages\RepositoryInterface;
use Arikaim\Core\Utils\Url;
use Arikaim\Core\Utils\ZipFile;
use Arikaim\Core\Arikaim;
use Arikaim\Core\System\Path;

/**
 * Package repository base class //TODO
*/
class LibraryRepository extends PackageRepository
{
    /**
     * Constructor
     *
     * @param string $repositoryUrl
     * @param string $currentVersion
     */
    public function __construct($repositoryUrl, $currentVersion)
    {
        parent::__construct($repositoryUrl,$currentVersion);
    }
    
    /**
     * Install package
     *
     * @param string|null $version
     * @return void
     */
    public function install($version = null)
    {
        $this->download();

       // if (ZipFile::isValid($file_name) == false) {
    
       // }
        // extract
        //$destination_file = ($library_name == null) ? basename($file_name) : $library_name;


       // $result = ZipFile::extract($file_name,Path::STORAGE_TEMP_PATH);
    }
}