<?php

namespace App\Http\Controllers\Backend;

use App\Events\HistorialModificacionEvent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use AuditoriaOperaciones;
use Suscriptor;

class SubscribersController extends Controller
{

    public function __construct()
    {

    }

    public function list($proceso_id)
    {
        Log::info("Listando suscriptores para proceso id: " . $proceso_id);
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);
        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }
        $data['proceso'] = $proceso;
        $data['suscriptores'] = $data['proceso']->Suscriptores;
        $data['title'] = 'Triggers';

        return view('backend.subscribers.index', $data);
    }

    public function create($proceso_id)
    {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }

        $data['edit'] = FALSE;
        $data['proceso'] = $proceso;
        $data['suscriptor'] = new Suscriptor();
        $data['title'] = 'Registrar metodo';

        return view('backend.subscribers.edit', $data);
    }

    public function edit($suscriptor_id)
    {
        $suscriptor = Doctrine::getTable('Suscriptor')->find($suscriptor_id);
        if ($suscriptor->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }
        $data['edit'] = TRUE;
        $data['proceso'] = $suscriptor->Proceso;
        $data['suscriptor'] = $suscriptor;
        $data['title'] = 'Editar Suscriptor';

        return view('backend.subscribers.edit', $data);
    }

    public function edit_form(Request $request, $suscriptor_id = NULL)
    {
        $suscriptor = NULL;

        if ($suscriptor_id) {
            $suscriptor = Doctrine::getTable('Suscriptor')->find($suscriptor_id);
        } else {
            $suscriptor = new Suscriptor();
            $suscriptor->proceso_id = $request->input('proceso_id');
        }

        $extra = $request->input('extra');

        $tipoSeguridad = $extra['idSeguridad'];
        if ($suscriptor->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar esta accion.';
            exit;
        }

        $validations = [
            'institucion' => 'required',
            'extra.webhook' => 'required',
        ];
        $messages = [
            'institucion.required' => 'El campo Institucion es obligatorio',
            'extra.webhook.required' => 'El campo Webhook es obligatorio',
        ];

        $suscriptor->validateForm();

        $request->validate($validations, $messages);

        if (!$suscriptor_id) {
            $request->validate(['proceso_id' => 'required'], ['proceso_id.requird' => 'El campo Proceso es obligatorio.']);
        }

        if (!$suscriptor) {
            $request->validate(['proceso_id' => 'required'], ['proceso_id.requird' => 'El campo Proceso es obligatorio.']);
        }

        $suscriptor->institucion = $request->input('institucion');
        $suscriptor->extra = $request->input('extra', false);
        $suscriptor->save();

        event(new HistorialModificacionEvent(
            'Se ha creado o modificado un suscriptor [id: '.$suscriptor->id.']',
            $suscriptor->Proceso->id
        ));

        return response()->json([
            'validacion' => true,
            'redirect' => route('backend.subscribers.list', [$suscriptor->Proceso->id])
        ]);
    }

    public function eliminar($suscriptor_id)
    {
        $suscriptor = Doctrine::getTable('Suscriptor')->find($suscriptor_id);
        if ($suscriptor->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para eliminar esta accion.';
            exit;
        }
        $proceso = $suscriptor->Proceso;
        $fecha = new \DateTime();
        // Auditar
        $registro_auditoria = new AuditoriaOperaciones ();
        $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
        $registro_auditoria->operacion = 'Eliminación de Suscriptor';
        $usuario = Auth::user();
        $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';
        $registro_auditoria->proceso = $proceso->nombre;
        $registro_auditoria->cuenta_id = Auth::user()->cuenta_id;
        //Detalles
        $suscriptor_array['proceso'] = $proceso->toArray(false);
        $suscriptor_array['suscriptor'] = $suscriptor->toArray(false);
        unset($suscriptor_array['suscriptor']['proceso_id']);
        $registro_auditoria->detalles = json_encode($suscriptor_array);
        $registro_auditoria->save();
        $suscriptor->delete();

        event(new HistorialModificacionEvent(
            'Se eliminó un suscriptor [id: '.$suscriptor->id.']',
            $suscriptor->Proceso->id
        ));

        return redirect()->route('backend.subscribers.list', [$proceso->id]);
    }

    public function export($suscriptor_id)
    {

        $suscriptor = Doctrine::getTable('Suscriptor')->find($suscriptor_id);

        $json = $suscriptor->exportComplete();

        header("Content-Disposition: attachment; filename=\"" . mb_convert_case(str_replace(' ', '-', $suscriptor->institucion), MB_CASE_LOWER) . ".simple\"");
        header('Content-Type: application/json');
        echo $json;

    }

    public function import(Request $request)
    {
        try {
            $file_path = $_FILES['archivo']['tmp_name'];
            $proceso_id = $request->input('proceso_id');

            if ($file_path && $proceso_id) {
                $input = file_get_contents($_FILES['archivo']['tmp_name']);
                $suscriptor = Accion::importComplete($input, $proceso_id);
                $suscriptor->proceso_id = $proceso_id;
                $suscriptor->save();
            } else {
                die('No se especificó archivo o ID proceso');
            }
        } catch (Exception $ex) {
            die('Código: ' . $ex->getCode() . ' Mensaje: ' . $ex->getMessage());
        }

        return redirect($_SERVER['HTTP_REFERER']);
    }
}
