<?php

use App\Helpers\Doctrine;

class SeguridadIntegracion
{

    public function getConfigRest($id_seguridad, $server, $timeout, $crt) //, $crt
    {

        $tipo_seguridad = "none";

        Log::debug("Seguridad_integracion=> ".$crt);

        if (isset($id_seguridad) && strlen($id_seguridad) > 0 && $id_seguridad > 0) {
            $seguridad = Doctrine::getTable('Seguridad')->find($id_seguridad);
            $tipo_seguridad = $seguridad->extra->tipoSeguridad;
            $user = $seguridad->extra->user;
            $pass = $seguridad->extra->pass;
            $api_key = $seguridad->extra->apikey;
            $name_key = $seguridad->extra->namekey;
            $url_auth = $seguridad->extra->url_auth;
            $uri_auth = $seguridad->extra->uri_auth;
            $request_seg = $seguridad->extra->request_seg;
        }

        switch ($tipo_seguridad) {
            case "HTTP_BASIC":
                //Seguridad basic

                $config = array(
                    'timeout' => $timeout,
                    'base_uri' => $server,
                    'auth' => [$user, $pass],
                    'http_auth' => 'Basic',
                    'verify' => $crt
                );

                /*if(isset($crt)){
                    $config['cert'] = $crt;    //es para los .pem
                }*/

                break;
            case "API_KEY":
                //Seguriad api key
                $config = array(
                    'timeout' => $timeout,
                    'base_uri' => $server,
                    'api_key' => $api_key,
                    'api_name' => $name_key
                );
                break;
            case "OAUTH2":
                //SEGURIDAD OAUTH2
                $config_seg = array(
                    'base_uri' => $url_auth
                );

                $client = new GuzzleHttp\Client($config_seg);

                $result = $client->post($uri_auth, [
                    GuzzleHttp\RequestOptions::JSON => $request_seg
                ]);

                $statusCode = $result->getStatusCode();

                //Se obtiene la codigo de la cabecera HTTP
                if ($statusCode >= 200 && $statusCode < 300) {
                    $config = array(
                        'timeout' => $timeout,
                        'base_uri' => $server,
                        'api_key' => $result->token_type . ' ' . $result->access_token,
                        'api_name' => 'Authorization'
                    );
                }
                break;
            default:
                //SIN SEGURIDAD
                $config = array(
                    'timeout' => $timeout,
                    'base_uri' => $server
                );
                break;
        }
        return $config;
    }

    public function setSecuritySoap($client, $idSeguridad)
    {

        $data = Doctrine::getTable('Seguridad')->find($idSeguridad);
        $tipoSeguridad = $data->extra->tipoSeguridad;
        $user = $data->extra->user;
        $pass = $data->extra->pass;
        $ApiKey = $data->extra->apikey;

        //Se instancia el tipo de seguridad segun sea el caso
        switch ($tipoSeguridad) {
            case "HTTP_BASIC":
                //SEGURIDAD BASIC
                $client->setCredentials($user, $pass, 'basic');
                break;
            case "API_KEY":
                //SEGURIDAD API KEY
                $header =
                    "<SECINFO>
                  <KEY>" . $this->extra->apikey . "</KEY>
                </SECINFO>";
                $client->setHeaders($header);
                break;
            case "OAUTH2":
                //SEGURIDAD OAUTH2
                $client->setCredentials($user, $pass, 'basic');
                break;
            default:
                //NO TIENE SEGURIDAD
                break;
        }

        return $client;

    }
}