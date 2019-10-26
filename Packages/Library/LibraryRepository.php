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

    public function __construct($repository)
    {
        parent::__construct($repository);
    }
    
    public function download()
    {
      //  $license_key = (emty($license_key) == true) ? "" : $license_key;
      //  $url = Url::REPOSITORY_URL . '/download/library/' . $name . "/$license_key";
    }

    public function getLastVersion()
    {
       // $url = Url::REPOSITORY_URL . '/library/' . $name;
       
    }

    public function install()
    {
       // if (ZipFile::isValid($file_name) == false) {
    
       // }
        // extract
        //$destination_file = ($library_name == null) ? basename($file_name) : $library_name;


       // $result = ZipFile::extract($file_name,Path::STORAGE_TEMP_PATH);
    }
}