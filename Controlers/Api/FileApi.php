<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c) 2017-2018 Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license.html
 * 
*/
namespace Arikaim\Core\Controlers\Api;

use Arikaim\Core\Controlers\ApiControler;
use Arikaim\Core\Install\Install;

class FileApi extends ApiControler
{
    public function upload($request, $response, $args) 
    {           
        $uploaded_files = $request->getUploadedFiles();
        $files = $uploaded_files['file'];
        if (empty($files) == true) {
            // error missing file
            $this->setApiError(Arikaim::error()->getError("UPLOAD_ERR_NO_FILE")); 
        }
        
        if (is_array($files) == true) {
            foreach ($files as $file) {
                $upload_error = $file->getError();
                if ($upload_error == UPLOAD_ERR_OK) {
                    // move uploaded file
                } else {
                    // error upoading file
                    $error = Arikaim::error()->getUplaodFileError($upload_error);
                    $this->setApiError($error);
                }
            }
        }
        return $this->getApiResponse();
    }
}
