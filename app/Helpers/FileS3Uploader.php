<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use function GuzzleHttp\json_decode;


class FileS3Uploader
{
    private $multipart_key = null;
    private $allowedExtensions = array();
    public static $sizeLimit = 20 * 1024 * 1024;
    private $tramite_id;
    public static $file_tipo = 's3';
    public static $amazon_algo = 'md5';
    public $filename = null;
    private $bucket_path;
    private $campo_id;

    function __construct(array $allowedExtensions = array(), $tramite_id, $filename, $campo_id)
    {
        $this->tramite_id = $tramite_id;
        $this->allowedExtensions = array_map("strtolower", $allowedExtensions);
        $this->filename = self::filenameToAscii($filename);
        $this->multipart_key = $tramite_id.'/'.$this->filename;
        $this->bucket_path = $tramite_id.'/'.$filename;
        $this->campo_id = $campo_id;
    }

    private function toBytes($str)
    {
        $val = trim($str);
        $last = strtolower($str[strlen($str) - 1]);
        $val = preg_replace('/[^0-9]/', '', $val);

        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    private function createMultiPartId($client){
        $response = $client->CreateMultipartUpload(
            [
                'Bucket'=> env('AWS_BUCKET'),
                'Key'=> $this->multipart_key
            ]
        );

        if($response) {
            $file = Doctrine::getTable('File')->findOneByFilenameAndTipoAndTramiteId($this->filename, self::$file_tipo, $this->tramite_id);
            if( ! $file ){
                $file = new \File();
                $file->filename = $this->filename;
                $file->llave = strtolower(str_random(12));
                $file->tramite_id = $this->tramite_id;
                $file->tipo = self::$file_tipo;
                $file->campo_id = $this->campo_id;
            }
            $multipart_id = $response['UploadId'];
            $aux = json_decode(json_encode($file->extra), true);
            $aux['multipart_id'] = $multipart_id;
            $aux['file_name'] = $this->filename;
            $file->extra = $aux;
            $file->save();
            return $multipart_id;
        }
        $err_msg = 'Ocurrió un error al obtener el identificador de parte.';
        return ['error'=> $err_msg, 'success' => false];
    }

    private function getMultiPartId(){
        $file = Doctrine::getTable('File')->findOneByFilenameAndTipoAndTramiteId($this->filename, self::$file_tipo, $this->tramite_id);
        if($file && isset($file->extra->multipart_id)){
            return $file->extra->multipart_id;
        }
        $err_msg = 'Ocurrió un error al obtener el identificador de parte almacenado.';
        return array('error'=> $err_msg, 'success'=> false);
    }

    function uploadPart($etapa_id, $part_number, $total_segments){
        $data = self::readFromSTDIN();
        $disk = Storage::disk('s3');
        $driver = $disk->getDriver();
        $client = $driver->getAdapter()->getClient();

        if($part_number==1){
            $multipart_id = $this->createMultiPartId($client);
        }else{
            $multipart_id = $this->getMultiPartId();
        }
        if(is_array($multipart_id) && isset($multipart_id['success']) && ! $multipart_id['sucess']){
            return $multipart_id;
        }

        $file = Doctrine::getTable('File')->findOneByFilenameAndTipoAndTramiteId($this->filename, self::$file_tipo, $this->tramite_id);
        if(! $file ){
            Log::error('No se encontro en la base el registro correspondiente al archivo que esta siendo cargado.');
            $err_msg = 'Ocurrió un error al iniciar la carga de archivo.';
            return ['error'=> $err_msg, 'success'=> false];
        }
        $result = $client->uploadPart([
            'Bucket'=> env('AWS_BUCKET'),
            'Key'   => $this->multipart_key,
            'UploadId'   => $multipart_id,
            'PartNumber' => $part_number,
            'Body'       => $data
        ]);

        $extra_arr = json_decode(json_encode($file->extra),true);

        $extra_arr['parts'][$part_number] = [
            'ETag' => str_replace('"', '', $result['ETag']),
            'PartNumber' => intval($part_number),
            'Size' => strlen($data)
        ];
        
        $file->extra = $extra_arr;
        $file->save();
        if($part_number == $total_segments){
            $result = $client->CompleteMultipartUpload([
                'Bucket'=> env('AWS_BUCKET'),
                'Key'=> $this->multipart_key,
                'UploadId'   => $multipart_id,
                'MultipartUpload' => ['Parts' => json_decode(json_encode($file->extra->parts), true)]
            ]);
            
            // if(array_key_exists('success', $result) && $result['success'] == true){
            if(isset($result['@metadata'])&& $result['@metadata']['statusCode'] == 200){
                $result['id'] = $file->id;
                $result['llave'] = $file->llave;
                $result['file_name'] = $file->filename;
            }else{
                Log::error($result);
                $err_msg = 'Ocurrió un error al completar la carga del archivo.';
                return ['error'=> $err_msg, 'success'=> false];
            }

            $file = Doctrine::getTable('File')->findOneByFilenameAndTipoAndTramiteId($this->filename, self::$file_tipo, $this->tramite_id);
            
            $s3_multipart_metadata = $driver->getAdapter()->getMetadata($this->multipart_key);
            $extra_arr['s3_filepath'] = $s3_multipart_metadata['dirname'];
            $extra_arr['s3_bucket'] = $result['Bucket'];
            $extra_arr['s3_file_size'] = $s3_multipart_metadata['size'];
            $extra_arr['s3_mimetype'] = $s3_multipart_metadata['mimetype'];
            $file->extra = $extra_arr;
            $file->save();
        }
        $hash = str_replace('"', '', $result['ETag']);
        $hash = strpos($hash, '-') === FALSE ? $hash : substr($hash, 0, strpos($hash, '-'));
        return [
            'part_number' => $part_number,
            'success'=> $result['@metadata']['statusCode'] === 200 ? true: false,
            'file_name' => $this->filename,
            'URL' => '/uploader/datos_get_s3/'.$file->id.'/'.$this->campo_id. '/' . $file->llave,
            'hash' => $hash,
            'algorithm' => self::$amazon_algo
        ];
    }

    private static function readFromSTDIN(){
        $f_input = fopen("php://input", "rb");
        $buff = [];
        while (!feof($f_input)) {
            $buff[] = fread($f_input, 131070);
        }
        fclose($f_input);
        return implode($buff);
    }

    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function singlePartUpload()
    {
        $ext = pathinfo($this->filename, PATHINFO_EXTENSION);
        if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)) {
            $these = implode(', ', $this->allowedExtensions);
            $err_msg = 'Extensión de archivo inválida. Solo puedes subir archivos con estas extensiones: ' . $these . '.';
            return array('error' => $err_msg,
                        'success' => false);
        }

        $full_path = $this->tramite_id.'/'.$this->filename;
        $f_input = fopen("php://input", "r");

        $disk = Storage::disk('s3');
        $driver = $disk->getDriver();

        $metadata = ['Metadata' => ['tramite_id' => $this->tramite_id]];
        
        try{
            $status_bool = $disk->put($full_path, $f_input, $metadata);
        }catch(\Aws\S3\Exception\S3Exception $e){
            Log::error($e);
            $status_bool = false;
            $err_msg = 'Ocurrió un error con S3 durante la carga del archivo.';
            Log::error($err_msg);
        }catch(\Exception $e){
            Log::error($e);
            $status_bool = false;
            $err_msg = 'Ocurrió un error durante la carga del archivo.';
            Log::error($err_msg);
        }

        if ($status_bool) {
            $aws_metadata = $driver->getAdapter()->getMetadata($full_path);
            $file = Doctrine::getTable('File')->findOneByFilenameAndTipoAndTramiteId($this->filename, self::$file_tipo, $this->tramite_id);
            if( ! $file ){
                $file = new \File();
                $file->tipo = self::$file_tipo;
                $file->llave = strtolower(str_random(12));
                $file->tramite_id = $this->tramite_id;
                $file->filename = $this->filename;
                $file->campo_id = $this->campo_id;
                $file->save();
            }
            
            $hash = str_replace('"', '',$aws_metadata['etag']);
            $extra_arr = [];

            $extra_arr['file_name'] = $this->filename;
            $extra_arr['s3_bucket'] = env('AWS_BUCKET');  // no viene en los metadatos
            $extra_arr['s3_filepath'] = $aws_metadata['dirname'];
            
            $extra_arr['s3_file_size'] = $aws_metadata['size'];
            $extra_arr['s3_mimetype'] = $aws_metadata['mimetype'];
            $extra_arr['hash'] = $hash;
            $file->extra = $extra_arr;
            $file->save();
          
            return [
                'status'=> $status_bool,
                'part_number' => 1,
                'success'=> true,
                'file_name' => $this->filename,
                'URL' => '/uploader/datos_get_s3/'.$file->id.'/'.$this->campo_id.'/' . $file->llave,
                'hash' => str_replace('"', '',$aws_metadata['etag']),
                'algorithm' => self::$amazon_algo
            ];
        } 
        
        return ['error' => $status_bool, 'success' => false];
    }

    public static function filenameToAscii($filename){
        $filename = preg_replace('/\s+/', ' ', $filename);  //Le hacemos un trim
        //$filename = sha1(uniqid(mt_rand(),true));
        $filename = trim($filename);
        $filename = str_replace(array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),$filename);
        $filename = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),$filename);
        $filename = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),$filename);
        $filename = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $filename);
        $filename = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),$filename);
        $filename = str_replace(array('ñ', 'Ñ', 'ç', 'Ç'),array('n', 'N', 'c', 'C',), $filename);
        $filename = str_replace(array("\\","¨","º","-","~","#","@","|","!","\"","·","$","%","&","/","(", ")","?","'","¡","¿","[","^","`","]","+","}","{","¨","´",">","< ",";", ",",":"," "),'',$filename);
        return $filename;
    }
}
