<?php
require_once('accion.php');

use Illuminate\Support\Facades\Log;
use App\Helpers\Doctrine;

class AccionWebservice extends Accion
{

    public function displayForm()
    {
        $display = '<p>Esta accion consultara via REST la siguiente URL. Los resultados, seran almacenados como variables.</p>';
        $display .= '<p>Los resultados esperados deben venir en formato JSON siguiendo este formato:</p>';
        $display .= '<pre>
{
    "variable1": "valor1",
    "variable2": "valor2",
    ...
}</pre>';
        $display .= '<div class="form-group">';
        $display .= '<label for="url">URL</label>';
        $display .= '<input type="text" id="url" class="form-control col-6" name="extra[url]" value="' . ($this->extra ? $this->extra->url : '') . '" />';
        $display .= '</div>';


        return $display;
    }

    public function validateForm(Request $request)
    {
        $request->validate([
            'extra.url' => 'required'
        ], [
            'extra.url.required' => 'El campo URL es obligatorio'
        ]);
    }

    public function ejecutar($tramite_id)
    {
        $etapa = $tramite_id;
        Log::info('Ejecutar webservice');
        $r = new Regla($this->extra->url);
        $url = $r->getExpresionParaOutput($etapa->id);

        //Hacemos encoding a la url
        $url = preg_replace_callback('/([\?&][^=]+=)([^&]+)/', function ($matches) {
            $key = $matches[1];
            $value = $matches[2];
            return $key . urlencode($value);
        },
            $url);

        $ch = curl_init();
        Log::info('URL: ' . $url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        Log::info('Result: ' . $result);

        $json = json_decode($result);

        foreach ($json as $key => $value) {
            $dato = \App\Helpers\Doctrine::getTable('DatoSeguimiento')->findOneByNombreAndEtapaId($key, $etapa->id);
            if (!$dato)
                $dato = new DatoSeguimiento();
            $dato->nombre = $key;
            $dato->valor = $value;
            $dato->etapa_id = $etapa->id;
            $dato->save();
        }

    }

}
