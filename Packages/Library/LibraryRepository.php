<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Packages\Library;

use Arikaim\Core\Packages\PackageRepository;
use Arikaim\Core\Interfaces\Packages\RepositoryInterface;
use Arikaim\Core\Utils\Url;
use Arikaim\Core\Arikaim;

/**
 * Package repository base class
*/
class LibraryRepository extends PackageRepository
{
   

    public function __construct()
    {
    }
    
    public function download($name, $license_key = null)
    {
        $license_key = (emty($license_key) == true) ? "" : $license_key;
        $url = Url::REPOSITORY_URL . '/download/library/' . $name . "/$license_key";
         //Arikaim::http()->
    }

    public function getVersion($name)
    {
        $url = Url::REPOSITORY_URL . '/library/' . $name;
        //Arikaim::http()->
    }
}