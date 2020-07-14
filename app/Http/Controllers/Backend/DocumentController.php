<?php

namespace App\Http\Controllers\Backend;

use App\Events\HistorialModificacionEvent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\Doctrine;
use Doctrine_Query;
use AuditoriaOperaciones;
use Documento;

class DocumentController extends Controller
{

    public function list($proceso_id)
    {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }
        $data['proceso'] = $proceso;
        $data['documentos'] = $data['proceso']->Documentos;

        $data['title'] = 'Documentos';

        return view('backend.document.index', $data);
    }

    public function create($proceso_id)
    {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'No tiene permisos para crear este documento';
            exit;
        }

        $data['edit'] = FALSE;
        $data['proceso'] = $proceso;
        $data['title'] = 'Edición de Documento';

        return view('backend.document.edit', $data);
    }

    public function edit($documento_id)
    {
        $documento = Doctrine::getTable('Documento')->find($documento_id);

        if ($documento->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'No tiene permisos para editar este documento';
            exit;
        }

        $data['documento'] = $documento;
        $data['edit'] = TRUE;
        $data['proceso'] = $documento->Proceso;
        $data['title'] = 'Edición de Documento';

        return view('backend.document.edit', $data);
    }

    public function edit_form(Request $request, $documento_id = NULL)
    {
        $documento = NULL;

        if ($documento_id) {
            $documento = Doctrine::getTable('Documento')->find($documento_id);
        } else {
            $documento = new Documento();
            $documento->proceso_id = $request->input('proceso_id');
        }

        if ($documento->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar este documento.';
            exit;
        }

        $request->validate([
            'nombre' => 'required',
            'tipo' => 'required',
            'contenido' => 'required',
        ]);

        if ($request->input('tipo') == 'certificado') {

            $request->validate([
                'titulo' => 'required',
                'subtitulo' => 'required',
                'servicio' => 'required',
                'servicio_url' => 'required',
            ]);


            /*$this->form_validation->set_rules('titulo', 'Título', 'required');
            $this->form_validation->set_rules('subtitulo', 'Subtítulo', 'required');
            $this->form_validation->set_rules('servicio', 'Servicio', 'required');
            $this->form_validation->set_rules('servicio_url', 'URL del Servicio', 'required|prep_url');
            $this->form_validation->set_rules('firmador_nombre', 'Nombre del firmador');
            $this->form_validation->set_rules('firmador_cargo', 'Cargo del firmador');
            $this->form_validation->set_rules('firmador_servicio', 'Servicio del firmador');
            $this->form_validation->set_rules('firmador_imagen', 'Imagen de la firmas');
            $this->form_validation->set_rules('validez', 'Dias de validez', 'is_natural');
            $this->form_validation->set_rules('validez_habiles', 'Habiles');
            */
        }
        $servicio_url = prep_url($request->input('servicio_url'));

        $documento->nombre = $request->input('nombre');
        $documento->tipo = $request->input('tipo');
        $documento->contenido = $request->input('contenido', false);
        $documento->tamano = $request->input('tamano');
        $documento->hsm_configuracion_id = $request->input('hsm_configuracion_id');

        if ($documento->tipo == 'certificado') {
            $documento->validez = $request->input('validez') == '' ? null : $request->input('validez');
            $documento->validez_habiles = $request->input('validez_habiles');
        }

        $documento->timbre = $request->has('timbre') && !is_null($request->input('timbre')) ? $request->input('timbre') : '';
        $documento->logo = $request->has('logo') && !is_null($request->input('logo')) ? $request->input('logo') : '';
        $documento->servicio = $request->has('servicio') && !is_null($request->input('servicio')) ? $request->input('servicio') : '';
        $documento->servicio_url = $servicio_url;
        $documento->firmador_nombre = $request->has('firmador_nombre') && !is_null($request->input('firmador_nombre')) ? $request->input('firmador_nombre') : '';
        $documento->firmador_cargo = $request->has('firmador_cargo') && !is_null($request->input('firmador_cargo')) ? $request->input('firmador_cargo') : '';
        $documento->firmador_servicio = $request->has('firmador_servicio') && !is_null($request->input('firmador_servicio')) ? $request->input('firmador_servicio') : '';
        $documento->firmador_imagen = $request->has('firmador_imagen') && !is_null($request->input('firmador_imagen')) ? $request->input('firmador_imagen') : '';
        $documento->subtitulo = $request->has('subtitulo') && !is_null($request->input('subtitulo')) ? $request->input('subtitulo') : '';
        $documento->subtitulo = $request->has('subtitulo') && !is_null($request->input('subtitulo')) ? $request->input('subtitulo') : '';
        $documento->titulo = $request->has('titulo') && !is_null($request->input('titulo')) ? $request->input('titulo') : '';

        $documento->save();

        event(new HistorialModificacionEvent(
            'Se a creado o modificado un documento [id: '.$documento->id.']',
            $documento->proceso_id
        ));

        return response()->json([
            'validacion' => true,
            'redirect' => route('backend.document.list', [$documento->Proceso->id])
        ]);
    }

    public function preview($documento_id)
    {
        $documento = Doctrine::getTable('Documento')->find($documento_id);

        if ($documento->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos';
            exit;
        }

        $documento->previsualizar();
    }


    public function destroy($documento_id)
    {
        $documento = Doctrine::getTable('Documento')->find($documento_id);

        if ($documento->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para eliminar este documento.';
            exit;
        }

        $proceso = $documento->Proceso;
        $fecha = new \DateTime();

        // Auditar
        $registro_auditoria = new \AuditoriaOperaciones();
        $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
        $registro_auditoria->operacion = 'Eliminación de Documento';
        $usuario = Auth::user();
        $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';
        $registro_auditoria->proceso = $proceso->nombre;
        $registro_auditoria->cuenta_id = Auth::user()->cuenta_id;

        // Detalles
        $documento_array['proceso'] = $proceso->toArray(false);

        $documento_array['documento'] = $documento->toArray(false);
        unset($documento_array['documento']['proceso_id']);

        if ($documento->hsm_configuracion_id)
            $documento_array['hsm_configuracion'] = $documento->HsmConfiguracion->toArray(false);

        unset($documento_array['hsm_configuracion_id']);

        $registro_auditoria->detalles = json_encode($documento_array);
        $registro_auditoria->save();

        $documento->delete();

        event(new HistorialModificacionEvent(
            'Se eliminó un documento [id: '.$documento->id.']',
            $documento->proceso_id
        ));

        return redirect()->route('backend.document.list', [$proceso->id]);

    }

    public function export($documento_id)
    {
        $documento = Doctrine::getTable('Documento')->find($documento_id);

        $json = $documento->exportComplete();

        header("Content-Disposition: attachment; filename=\"" . mb_convert_case(str_replace(' ', '-', $documento->nombre), MB_CASE_LOWER) . ".simple\"");
        header('Content-Type: application/json');
        echo $json;

        event(new HistorialModificacionEvent(
            'Se exportó un documento [id: '.$documento->id.']',
            $documento->proceso_id
        ));
    }

    public function import(Request $request)
    {
        try {
            $file_path = $_FILES['archivo']['tmp_name'];
            $proceso_id = $request->input('proceso_id');

            if ($file_path && $proceso_id) {
                $input = file_get_contents($_FILES['archivo']['tmp_name']);
                $documento = \Documento::importComplete($input, $proceso_id);
                $documento->proceso_id = $proceso_id;
                $documento->save();

                event(new HistorialModificacionEvent(
                    'Se importó un nuevo documento [id: '.$documento->id.']',
                    $documento->proceso_id
                ));
            } else {
                die('No se especificó archivo o ID proceso');
            }
        } catch (\Exception $ex) {
            die('Código: ' . $ex->getCode() . ' Mensaje: ' . $ex->getMessage());
        }

        return redirect($_SERVER['HTTP_REFERER']);
    }
}
