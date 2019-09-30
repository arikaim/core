<?php
/**
 *  Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2019 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Controllers\Api\Ui;

use Arikaim\Core\Controllers\ApiController;
use Arikaim\Core\Arikaim;
use Arikaim\Core\View\Html\HtmlComponent;
use Arikaim\Core\Packages\Library\LibraryRepository;
use Arikaim\Core\System\Path;

/**
 * Ui library upload Api controller
*/
class Library extends ApiController
{
    /**
     * Get html component details
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
     */
    public function upload($request, $response, $data)
    {
        // control panel only
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) use($request) {             
            $files = $request->getUploadedFiles();
            if (isset($files['file']) == true) {
                $file = $files['file'];
            } else {
                $this->withError("Upload file error")->getResponse();           
            }

            if ($file->getError() === UPLOAD_ERR_OK) {
                $file_name = $file->getClientFilename();
                $dstination = Path::STORAGE_TEMP_PATH . $file->getClientFilename();
                $file->moveTo($dstination);

                $result = LibraryRepository::unpack($dstination);
                if ($result == false) {
                    $this->setError("Not valid zip arhive");
                }
            }

            $this->setResult(['file_name' => $file_name]); 
        });
        $data->validate();
    }
}
