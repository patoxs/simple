<?php

namespace App\Http\Controllers;

use App\Helpers\Doctrine;
use Illuminate\Http\Request;
use Doctrine_Query;
use Illuminate\Support\Facades\Auth;
use App\Helpers\FileUploader;
use App\Helpers\FileS3Uploader;
use App\Helpers\File;
use Illuminate\Support\Facades\Log;
use mysql_xdevapi\Exception;
use App\Models\DatoSeguimiento;


class UploadController extends Controller
{

    public function datos_s3(Request $request, $campo_id, $etapa_id, $multipart=null, $part_number=null, $total_segments=null){
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        if (Auth::user()->id != $etapa->usuario_id) {
            echo 'Usuario no tiene permisos para subir archivos en esta etapa';
            exit;
        }

        $tramite_id = $etapa->tramite_id;

        $campo = Doctrine_Query::create()
            ->from('Campo c, c.Formulario.Pasos.Tarea.Etapas e')
            ->where('c.id = ? AND e.id = ?', array($campo_id, $etapa_id))
            ->fetchOne();
        if (!$campo) {
            echo 'Campo no existe';
            exit;
        }

        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array('gif', 'jpg', 'png', 'pdf', 'doc', 'docx', 'zip', 'rar', 'ppt', 'pptx', 'xls', 'xlsx', 'mpp', 'vsd', 'odt', 'odp', 'ods', 'odg');
        if (isset($campo->extra->filetypes)) {
            $allowedExtensions = $campo->extra->filetypes;
        }

        if(!$request->headers->has('filename')){
            die('No se envio el nombre de archivo.');
        }

        $filename = urldecode($request->header('filename'));

        if($request->header('content-length') > FileS3Uploader::$sizeLimit){
            echo json_encode(['error' => 'El archivo es muy grande'], JSON_UNESCAPED_UNICODE);
            exit;
        }else if(! $request->hasHeader('content-length')){
            echo json_encode(['error'=>'Cabeceras no contienen content-length'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $filename = $campo->id.$filename;

        if( ! is_null($multipart) && $multipart == 'multi'){
            $s3_uploader = new FileS3Uploader($allowedExtensions, $tramite_id, $filename, $campo->id);
            $result = $s3_uploader->uploadPart($etapa_id, $part_number, $total_segments);
        }else{
            $s3_uploader = new FileS3Uploader($allowedExtensions, $tramite_id, $filename, $campo->id);
            $result = $s3_uploader->singlePartUpload();
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function datos(Request $request, $campo_id, $etapa_id, $multipart=null, $part_number=null)
    {
        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
        if (Auth::user()->id != $etapa->usuario_id) {
            echo 'Usuario no tiene permisos para subir archivos en esta etapa';
            exit;
        }

        $tramite_id = $etapa->tramite_id;

        $campo = Doctrine_Query::create()
            ->from('Campo c, c.Formulario.Pasos.Tarea.Etapas e')
            ->where('c.id = ? AND e.id = ?', array($campo_id, $etapa_id))
            ->fetchOne();
        if (!$campo) {
            echo 'Campo no existe';
            exit;
        }

        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array('gif', 'jpg', 'png', 'pdf', 'doc', 'docx', 'zip', 'rar', 'ppt', 'pptx', 'xls', 'xlsx', 'mpp', 'vsd', 'odt', 'odp', 'ods', 'odg');
        if (isset($campo->extra->filetypes)) {
            $allowedExtensions = $campo->extra->filetypes;
        }

        // max file size in bytes
        $sizeLimit = 20 * 1024 * 1024;

        $uploader = new FileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload('uploads/datos/');

        if (isset($result['success'])) {
            $file = new \File();
            $file->tramite_id = $etapa->Tramite->id;
            $archivo = $result['file_name'];
            $archivo = trim($archivo);
            $archivo = str_replace(array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $archivo);
            $archivo = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $archivo);
            $archivo = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $archivo);
            $archivo = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $archivo);
            $archivo = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $archivo);
            $archivo = str_replace(array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $archivo);
            $archivo = str_replace(array("\\", "¨", "º", "-", "~", "#", "@", "|", "!", "\"", "·", "$", "%", "&", "/", "(", ")", "?", "'", "¡", "¿", "[", "^", "`", "]", "+", "}", "{", "¨", "´", ">", "< ", ";", ",", ":", " "), '', $archivo);
            //$file->filename=$result['file_name'];
            $file->filename = $archivo;
            $result['file_name'] = $archivo;
            $file->tipo = 'dato';
            $file->llave = strtolower(str_random(12));
            $file->save();

            $result['id'] = $file->id;
            $result['llave'] = $file->llave;
        }
        // to pass data through iframe you will need to encode all html tags
        //echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    private static function readFromSTDIN(){
        $f_input = fopen("php://input", "rb");
        $buff = [];
        while (!feof($f_input)) {
            $buff[] = fread($f_input, 4096);
        }
        fclose($f_input);
        return implode($buff);
    }

    public function datos_get($id, $token, $usuario_backend = null)
    {
        //Chequeamos los permisos en el frontend
        $file = Doctrine_Query::create()
            ->from('File f, f.Tramite t, t.Etapas e, e.Usuario u')
            ->where('f.id = ? AND f.llave = ? AND u.id = ?', array($id, $token, Auth::user()->id))
            ->fetchOne();
        if (!$file) {
            //Chequeamos permisos en el backend
            $file = Doctrine_Query::create()
                ->from('File f, f.Tramite.Proceso.Cuenta.UsuariosBackend u')
                ->where('f.id = ? AND f.llave = ? AND u.id = ? AND (u.rol like "%super%" OR u.rol like "%operacion%" OR u.rol like "%seguimiento%")', array($id, $token, $usuario_backend))
                ->fetchOne();

            if (!$file) {
                echo 'Usuario no tiene permisos para ver este archivo.';
                exit;
            }
        }

        $path = public_path('uploads/datos/' . $file->filename);
        if (preg_match('/^\.\./', $file->filename)) {
            echo 'Archivo invalido';
            exit;
        }

        if (!file_exists($path)) {
            echo 'Archivo no existe';
            exit;
        }

        return response()->download($path);
    }

    public function datos_get_s3(Request $request, $id, $campo_id, $token)
    {
        // $request->session()->flash('error', 'No se encontraron los archivos para descargar.');
        // return \Redirect::back();
        
        $file = Doctrine_Query::create()
            ->from('File f, f.Tramite t, t.Etapas e, e.Usuario u')
            ->where('f.id = ? AND f.llave = ? AND f.campo_id = ? ', array($id, $token, $campo_id))
            ->fetchOne();
        
        if (!$file) {
            abort(404, 'Archivo no encontrado.');
        }

        $full_path_filename = 's3://'.$file->extra->s3_bucket.'/'.$file->extra->s3_filepath .'/'.$file->extra->file_name;
        $remote_full = $file->extra->s3_filepath .'/'.$file->extra->file_name;
        
        header('Content-Type: ' . $file->extra->s3_mimetype);
        if(isset($file->extra->s3_file_size))
            header('Content-Length: ' . $file->extra->s3_file_size);
        
        header('Content-Disposition: attachment; filename="'.$file->extra->file_name.'"');
        
        $disk = \Storage::disk('s3');
        
        $driver = $disk->getDriver();
        $client = $driver->getAdapter()->getClient();
        $client->registerStreamWrapper();
        if ($read_stream = fopen($full_path_filename, 'r')) {
            while (!feof($read_stream)) {
                $leido = fread($read_stream, 16384);
                if($leido !== FALSE){
                    echo $leido;
                }else{
                    // error ftp 401, no de http
                    abort(401, 'S3 file read error.');
                }
            }
            fflush($read_stream);
            fclose($read_stream);
        }else{
            abort(404, 'Error al abrir el archivo para lectura.');
        }
    }
}
