<?php

class File extends Doctrine_Record
{

    function setTableDefinition()
    {
        $this->hasColumn('id');
        $this->hasColumn('filename');
        $this->hasColumn('tipo');
        $this->hasColumn('llave');          //Llave para ver el documento
        $this->hasColumn('llave_copia');    //Llave para obtener la copia del documento
        $this->hasColumn('llave_firma');    //Llave para poder firmar con token el documento
        $this->hasColumn('validez');
        $this->hasColumn('validez_habiles');
        $this->hasColumn('tramite_id');
        if(\Schema::hasColumn('file', 'extra')){
            $this->hasColumn('extra');
        }
        if(\Schema::hasColumn('file', 'campo_id')){
            $this->hasColumn('campo_id');
        }
    }

    function setUp()
    {
        parent::setUp();

        $this->actAs('Timestampable');

        $this->hasOne('Tramite', array(
            'local' => 'tramite_id',
            'foreign' => 'id'
        ));
    }

    public function postDelete($event)
    {
        parent::postDelete($event);
        if ($this->tipo == 'documento') {
            if (file_exists('uploads/documentos/' . $this->filename)) {
                unlink('uploads/documentos/' . $this->filename);
            }
            if (file_exists('uploads/documentos/' . preg_replace('/\.pdf$/', '.copia.pdf', $this->filename))) {
                unlink('uploads/documentos/' . preg_replace('/\.pdf$/', '.copia.pdf', $this->filename));
            }
        } else if ($this->tipo == 'dato') {
            unlink('uploads/datos/' . $this->filename);
        }
    }

    public static function saveFile($filename, $tramite_id, $data)
    {


        $filename = mb_strtolower($filename);   //Lo convertimos a minusculas
        $filename = preg_replace('/\s+/', ' ', $filename);  //Le hacemos un trim
        $filename = trim($filename);
        $parts = explode(".", $filename);


        $myfile = fopen("uploads/datos/" . $filename, "w");
        fwrite($myfile, base64_decode($data));
        fclose($myfile);

        // max file size in bytes
        //$sizeLimit = 20 * 1024 * 1024;
        //$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        //$result = $uploader->handleUpload('uploads/datos/');
        log_message("INFI", "Guardando archivo " + $filename, FALSE);
        $file = new File();
        $file->tramite_id = $tramite_id;
        $archivo = $filename;
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
        $file->llave = strtolower(random_string('alnum', 12));
        $file->save();

        $result['id'] = $file->id;
        $result['llave'] = $file->llave;


    }

    public function setExtra($extra)
    {
        if (is_string($extra)) {
            //Si es que no es un JSON lo que recibimos, lo codificamos nosotros.
            $val = json_decode($extra);
            if (!is_array($val) && !is_object($val))
                $extra = json_encode($extra);
        } else {
            $extra = json_encode($extra);
        }

        $this->_set('extra', $extra);
    }

    public function getExtra()
    {
        return json_decode($this->_get('extra'));
    }
}
