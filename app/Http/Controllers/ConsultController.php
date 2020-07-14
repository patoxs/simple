<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConsultController extends Controller
{
    public function index(Request $request)
    {
        $query = 0;
        $data = array();
        $data['vacio'] = '';
        $data['title'] = 'Consultas de Documentos';
        $data['titulo'] = 'Seguimiento de Trámites en Línea';
        $resp = '<br/><div class="alert alert-warning"><strong>Sin datos Disponibles</strong></div>';

        $nrotramite = trim($request->input('nrotramite', old('nrotramite')));

        if ($request->isMethod('post')) {
            $nrotramite = trim($request->input('nrotramite'));

            $request->validate([
                'nrotramite' => 'required|digits_between:1,30|numeric'
            ], [
                'nrotramite.numeric' => 'El campo <b>Nro. de Trámite</b> debe ser un número.',
                'nrotramite.required' => 'El campo <b>Nro. de Trámite</b> es obligatorio.',
                'digits_between' => 'El campo <b>Nro. de Trámite</b> debe contener entre :min y :max dígitos.'
            ]);

            if (is_numeric($nrotramite)) {
                $query = (new \Consultas)->listDatoSeguimiento($nrotramite, \Cuenta::cuentaSegunDominio());
                $data['vacio'] = $resp;
            }

        };

        $data['nrotramite'] = $nrotramite;
        $data['tareas'] = $query;

        return view('consult.index', $data);
    }

    public function ver_etapas($id_etapa)
    {
        $query = (new \Consultas)->detalleEtapa($id_etapa);

        $data['etapa'] = $query[0];
        $data['content'] = view('consult.consult_info', $data);

        return view('layouts.app', $data);
    }
}
