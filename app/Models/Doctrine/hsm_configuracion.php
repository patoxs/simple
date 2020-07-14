<?php

use Illuminate\Support\Facades\Log;

class HsmConfiguracion extends Doctrine_Record
{

    function setTableDefinition()
    {
        $this->hasColumn('id');
        $this->hasColumn('rut');
        $this->hasColumn('nombre');
        $this->hasColumn('cuenta_id');
        $this->hasColumn('entidad');
        $this->hasColumn('proposito');
        $this->hasColumn('estado');
    }

    function setUp()
    {
        parent::setUp();

        $this->hasOne('Cuenta', array(
            'local' => 'cuenta_id',
            'foreign' => 'id'
        ));

        $this->hasMany('Documento as Documentos', array(
            'local' => 'id',
            'foreign' => 'hsm_configuracion_id'
        ));
    }

    public function firmar($file_path, $entity, $rut, $expiration, $purpose, $otp = NULL)
    {
        try {
            Log::info('Iniciando procedimiento de firma, primer request => \n', [
                'run' => $rut,
                'entity' => $entity,
                "expiration" => $expiration,
                "purpose" => $purpose
            ]);

            $dataf = array(
                "entity" => $entity,
                "run" => $rut,
                "expiration" => $expiration,
                "purpose" => $purpose
            );
            Log::info('Proceso de firma para: \n\n', [
                'data' => $dataf
            ]);

        } catch(Exception $e) {
            Log::info('Error al leer los parametros iniciales para firmar (entity, run, expiration, purpose)', [
                'error' => $e
            ]);
            
            return false;
        }


        try {
            $data['token'] = JWT::encode($dataf, env('JWT_SECRET'));
            $url = env('JWT_URL_API_FIRMA');
            $data['api_token_key'] = env('JWT_API_TOKEN_KEY');
            $data['files'] = array(array(
                "content-type" => "application/pdf",
                "content" => base64_encode(file_get_contents($file_path)),
                "description" => "prueba 1",
                "checksum" => hash_file("sha256", $file_path)
            ));

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=UTF-8'));
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        } catch(Exception $e) {
            Log::info('Error en la peticion de firma \n\n', [
                'error' => $e
            ]);

            return false;
        }

        try {
            $result = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err != null)  {
                Log::info('Error al ejecutar la primera peticion a FIRMA:\n\n', [
                    'status' => $httpcode,
                    'error' => $err
                ]);

                return false;
            }

        } catch(Exception $e) {
            Log::info('Error al ejecutar la primera peticion a FIRMA:\n\n', [
                'error' => $e
            ]);

            return false;
        }

        // fin primera peticion
        
        if(env('API_FIRMA_VERSION')==1)
        {
            try {
                // procesando peticion
                $dataresult = json_decode($result);
                if(!isset($dataresult->session_token)){
                    Log::info('Error al ejecutar la primera peticion a FIRMA: Token inválido\n\n');
                    return false;
                }
                $session_token = $dataresult->session_token;

                Log::info('Continuando procedimiento de firma, segundo request => \n', [
                    'run' => $rut,
                    'entity' => $entity,
                ]);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url . "/" . $session_token);

                if (is_null($otp)) {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=UTF-8'));
                } else {
                    $headers = [
                        'Content-Type: application/json; charset=utf-8',
                        'OTP: ' . $otp
                    ];
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                }
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                // ejecutando segundo request
                $result = curl_exec($ch);

                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $err = curl_error($ch);
                curl_close($ch);

                if ($err != null)  {
                    Log::info('Error al ejecutar la segunda peticion a FIRMA:\n\n', [
                        'status' => $httpcode,
                        'error' => $err
                    ]);

                    return false;
                }

            } catch(Exception $e) {
                Log::info('Error al ejecutar la primera peticion a FIRMA:\n\n', [
                    'error' => $e
                ]);

                return false;
            }
        }


        try {
            if(env('API_FIRMA_VERSION')==1)
            {
                $dataresult = json_decode($result);
                $fileresult = $dataresult->files;
                $metadata = $dataresult->metadata;

                Log::info('Metadata Segunda peticion FIRMA:\n\n', [
                    'metadata' => $metadata
                ]);

                if ($metadata->files_signed == 1) {
                    foreach ($fileresult as $archivo) {
                        file_put_contents($file_path, base64_decode($archivo->content));
                    }
                    Log::info('El documento si ha sido firmado');
                    return true;
                } else {
                    Log::info('El documento no ha sido firmado');
                    return false;
                }
            }else{
                $dataresult = json_decode($result,true);
                $metadata = $dataresult["metadata"];
                $fileresult = $dataresult["files"];

                Log::info('Metadata peticion FIRMA:\n\n', [
                    'metadata' => $metadata
                ]);

                if ($metadata['filesSigned'] == 1) {
                    foreach ($fileresult as $archivo) {
                        file_put_contents($file_path, base64_decode($archivo["content"]));
                    }
                    Log::info('El documento si ha sido firmado');
                    return true;
                } else {
                    Log::info('El documento no ha sido firmado');
                    return false;
                }
            }
        } catch(Exception $e) {
            Log::info('Error al procesar la segunda peticion a FIRMA:\n\n', [
                'error' => $e
            ]);

            return false;
        }
    }

}
