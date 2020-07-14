<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\FileUploader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UploaderController extends Controller
{
    public function firma()
    {
        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array('png', 'jpg', 'gif');
        // max file size in bytes
        $sizeLimit = 20 * 1024 * 1024;

        $uploader = new FileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload('uploads/firmas/');

        // to pass data through iframe you will need to encode all html tags
        echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
    }

    public function firma_get($filename)
    {
        readfile('uploads/firmas/' . $filename);
    }

    public function timbre()
    {
        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array('png', 'jpg', 'gif');
        // max file size in bytes
        $sizeLimit = 20 * 1024 * 1024;

        $uploader = new FileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload('uploads/timbres/');

        // to pass data through iframe you will need to encode all html tags
        echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
    }

    public function timbre_get($filename)
    {
        readfile('uploads/timbres/' . $filename);
    }

    public function logo_certificado()
    {
        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array('png', 'jpg', 'gif');
        // max file size in bytes
        $sizeLimit = 20 * 1024 * 1024;

        $uploader = new FileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload('uploads/logos_certificados/');

        // to pass data through iframe you will need to encode all html tags
        echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
    }

    public function logo_certificado_get($filename)
    {
        readfile('uploads/logos_certificados/' . $filename);
    }

}
