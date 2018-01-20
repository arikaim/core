<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
 */
namespace Arikaim\Core\Install;

use Arikaim\Core\Utils\File;
use Arikaim\Core\Utils\Curl;
use Arikaim\Core\System\Config;
use Arikaim\Core\Utils\Utils;
use Arikaim\Core\Arikaim;
use Arikaim\Core\Db\Model;
use Arikaim\Core\Utils\ZipFile;

class Update 
{
    private $source_url;
    private $temp_ulr;

    private $valid_update_path;

    public function __construct() 
    {
        $this->source_url = "http://192.168.1.12/core.zip";
        $this->temp_ulr  = "";
        $this->valid_update_path = ['core','models','controlers'];
    }

    public function update()
    {
        $errors = 0;
        // check php zip extension
        if (ZipFile::isInstalled() == false) {
            throw new \Extension('Zip php extension not installed');
            return false;
        }
        $this->updateCore();
        $this->updateControlers();
        $this->updateModels();
        $this->updateUiLibrary();
        
        return $errors;
    }

    protected function updateCore()
    {
        // download zip file to temp folder
        $this->updatePath("core","core.zip");
    }

    protected function updateControlers()
    {
        
    }

    protected function updateModels()
    {
        
    }

    public function updateUiLibrary()
    {
        
    }

    private function updatePath($path, $file_name)
    {        
        if ($this->isValidUpdatePath($path) == false) {
            throw new \Exception("Not valid update path: $path");
            return false;
        }
        
        File::makeDir(File::getTempPath());

        $result = Curl::downloadFile($this->source_url,File::getTempPath(),$file_name);
      
        if ($result == false) {
            $result = File::downloadFile($this->source_url,File::getTempPath(),$file_name);
        }
        if ($result == false) {
            // Error downloading file
            return false;
        }

        // unzip file        
       // ZipFile::extract(File::getTempPath() . $file_name,File::getTempPath() . "core" . DIRECTORY_SEPARATOR);

        // replace files 


    }

    private function isValidUpdatePath($path)
    {
        return in_array($path,$this->valid_update_path);
    }
}
