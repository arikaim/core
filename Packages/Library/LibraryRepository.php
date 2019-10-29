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
use Arikaim\Core\Utils\ZipFile;

/**
 * Package repository base class 
*/
class LibraryRepository extends PackageRepository implements RepositoryInterface
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
        $result = $this->getRepositoryDriver()->download();

        if ($result == true) {
            //$result = ZipFile::extract($file_name,Path::STORAGE_TEMP_PATH);
            return true;
        }
     
        return false;
    }
}