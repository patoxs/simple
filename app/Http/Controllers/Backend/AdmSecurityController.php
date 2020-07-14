<?php

namespace App\Http\Controllers\Backend;

use App\Events\HistorialModificacionEvent;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use AuditoriaOperaciones;
use SeguridadForm;
use Seguridad;
use Accion;

class AdmSecurityController extends Controller
{

    /**
     * @param $proceso_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list($proceso_id)
    {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }

        $data['proceso'] = $proceso;
        $data['seguridad'] = $data['proceso']->Admseguridad;
        $data['title'] = 'Seguridad';

        return view('backend.security.index', $data);
    }

    /**
     * @param $proceso_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create($proceso_id)
    {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);
        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }

        $data['edit'] = FALSE;
        $data['proceso'] = $proceso;
        $data['seguridad'] = new Seguridad();
        $data['title'] = 'Registrar metodo';

        return view('backend.security.edit', $data);
    }

    /**
     * @param $seguridad_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($seguridad_id)
    {
        $seguridad = Doctrine::getTable('Seguridad')->find($seguridad_id);
        if ($seguridad->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }
        $data['edit'] = TRUE;
        $data['proceso'] = $seguridad->Proceso;
        $data['seguridad'] = $seguridad;
        $data['title'] = 'Editar Seguridad';

        return view('backend.security.edit', $data);
    }

    /**
     * @param Request $request
     * @param null $seguridad_id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function edit_form(Request $request, $seguridad_id = NULL)
    {
        $seguridad = NULL;
        if ($seguridad_id) {
            $seguridad = Doctrine::getTable('Seguridad')->find($seguridad_id);
        } else {
            $seguridad = new SeguridadForm();
            $seguridad->proceso_id = $request->input('proceso_id');
        }
        $extra = $request->input('extra');
        $tipoSeguridad = $extra['tipoSeguridad'];
        if ($seguridad->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar esta accion.';
            exit;
        }

        $validations = [
            'institucion' => 'required',
            'servicio' => 'required',
            'extra.tipoSeguridad' => 'required',
        ];

        $messages = [
            'institucion.required' => 'El campo Institución es obligatorio',
            'servicio.required' => 'El campo es Servicio obligatorio',
            'extra.tipoSeguridad.required' => 'El campo Tipo de seguridad es obligatorio',
        ];

        switch ($tipoSeguridad) {
            case 'API_KEY':
                $validations['extra.apikey'] = 'required';
                $messages['extra.apikey.required'] = 'El campo Llave de aplicación es obligatorio';
                break;
            case "HTTP_BASIC":
                $validations['extra.user'] = 'required';
                $validations['extra.pass'] = 'required';
                $messages['extra.user.required'] = 'El campo user es obligatorio';
                $messages['extra.pass.required'] = 'El campo password es obligatorio';
                break;
            case "OAUTH2":
                $validations['extra.url_auth'] = 'required';
                $validations['extra.request_seg'] = 'required';
                $messages['extra.url_auth.required'] = 'El campo Url de Autenticación es obligatorio';
                $messages['extra.request_seg.required'] = 'El campo Request es obligatorio';
                break;
        }


        $seguridad->validateForm();

        $request->validate($validations, $messages);

        $seguridad->institucion = $request->input('institucion');
        $seguridad->servicio = $request->input('servicio');
        $seguridad->extra = $request->input('extra', false);
        $seguridad->save();

        event(new HistorialModificacionEvent(
            'Se ha creado o modificado una configuración de seguridad [id: '.$seguridad->id.']',
            $seguridad->proceso_id
        ));

        return response()->json([
            'validacion' => true,
            'redirect' => route('backend.security.list', [$seguridad->Proceso->id]),
        ]);
    }

    /**
     * @param $seguridad_id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function eliminar($seguridad_id)
    {
        $seguridad = Doctrine::getTable('Seguridad')->find($seguridad_id);
        if ($seguridad->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para eliminar esta accion.';
            exit;
        }
        $proceso = $seguridad->Proceso;
        $fecha = new \DateTime();
        // Auditar
        $registro_auditoria = new AuditoriaOperaciones ();
        $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
        $registro_auditoria->operacion = 'Eliminación de Seguridad';
        $usuario = Auth::user();
        $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';
        $registro_auditoria->proceso = $proceso->nombre;
        $registro_auditoria->cuenta_id = Auth::user()->cuenta_id;
        //Detalles
        $seguridad_array['proceso'] = $proceso->toArray(false);
        $seguridad_array['seguridad'] = $seguridad->toArray(false);
        unset($seguridad_array['seguridad']['proceso_id']);
        $registro_auditoria->detalles = json_encode($seguridad_array);
        $registro_auditoria->save();
        $seguridad->delete();

        event(new HistorialModificacionEvent(
            'Se eliminó una configuración de seguridad [id: '.$seguridad->id.']',
            $seguridad->proceso_id
        ));

        return redirect('backend/Admseguridad/listar/' . $proceso->id);
    }

    /**
     * @param $seguridad_id
     */
    public function export($seguridad_id)
    {

        $seguridad = Doctrine::getTable('Seguridad')->find($seguridad_id);

        $json = $seguridad->exportComplete();

        header("Content-Disposition: attachment; filename=\"" . mb_convert_case(str_replace(' ', '-', $seguridad->institucion), MB_CASE_LOWER) . ".simple\"");
        header('Content-Type: application/json');
        echo $json;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function import(Request $request)
    {
        try {
            $file_path = $_FILES['archivo']['tmp_name'];
            $proceso_id = $request->input('proceso_id');

            if ($file_path && $proceso_id) {
                $input = file_get_contents($_FILES['archivo']['tmp_name']);
                $seguridad = Accion::importComplete($input, $proceso_id);
                $seguridad->proceso_id = $proceso_id;
                $seguridad->save();
            } else {
                die('No se especificó archivo o ID proceso');
            }
        } catch (Exception $ex) {
            die('Código: ' . $ex->getCode() . ' Mensaje: ' . $ex->getMessage());
        }

        return redirect($_SERVER['HTTP_REFERER']);
    }
}
