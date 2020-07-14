<?php

namespace App\Http\Controllers\Backend;

use App\Events\HistorialModificacionEvent;
use App\Models\Paso;
use App\Http\Controllers\Controller;
use App\Rules\CheckPermissionForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use Doctrine_Query;

class FormsController extends Controller
{

    public function list($proceso_id)
    {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }

        $data['proceso'] = $proceso;
        $data['formularios'] = $data['proceso']->Formularios;

        $data['title'] = 'Formularios';

        return view('backend.forms.index', $data);
    }

    public function create($proceso_id)
    {
        $proceso = Doctrine::getTable('Proceso')->find($proceso_id);

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para crear un formulario dentro de este proceso.';
            exit;
        }

        $formulario = new \Formulario();
        $formulario->proceso_id = $proceso->id;
        $formulario->nombre = 'Formulario';
        $formulario->save();

        event(new HistorialModificacionEvent(
            'Se creó un formulario',
            $formulario->proceso_id
        ));

        return redirect()->route('backend.forms.edit', [$formulario->id]);
    }

    public function destroy($formulario_id)
    {
        $formulario = Doctrine::getTable('Formulario')->find($formulario_id);

        if (!$formulario && $formulario->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para eliminar este formulario.';
            exit;
        }

        $proceso = $formulario->Proceso;

        $fecha = new \DateTime();

        // Auditar
        $registro_auditoria = new \AuditoriaOperaciones ();
        $registro_auditoria->fecha = $fecha->format("Y-m-d H:i:s");
        $registro_auditoria->operacion = 'Eliminación de Formulario';
        $usuario = Auth::user();
        $registro_auditoria->usuario = $usuario->nombre . ' ' . $usuario->apellidos . ' <' . $usuario->email . '>';
        $registro_auditoria->proceso = $proceso->nombre;
        $registro_auditoria->cuenta_id = Auth::user()->cuenta_id;


        // Detalles
        $formulario_array['proceso'] = $proceso->toArray(false);
        $formulario_array['formulario'] = $formulario->toArray(false);
        unset($formulario_array['formulario']['proceso_id']);

        foreach ($formulario->Campos as $campo) {
            $campo_array = $campo->toArray(false);
            if ($campo->documento_id != null)
                $campo_array['documento'] = $campo->Documento->nombre;
            unset($campo_array['documento_id']);
            $formulario_array['campos'][] = $campo_array;
        }

        $registro_auditoria->detalles = json_encode($formulario_array);
        $registro_auditoria->save();

        $formulario->delete();

        event(new HistorialModificacionEvent(
            'Se ha eliminado el formulario: [id: '.$formulario->id.']',
            $proceso->id
        ));

        return redirect()->route('backend.forms.list', [$proceso->id]);
    }


    public function edit($formulario_id)
    {


        $formulario = Doctrine::getTable('Formulario')->find($formulario_id);

        if ($formulario->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar este formulario.';
            exit;
        }

        $data['formulario'] = $formulario;
        $data['proceso'] = $formulario->Proceso;

        $data['title'] = $formulario->nombre;

        return view('backend.forms.edit', $data);
    }

    public function ajax_editar($formulario_id)
    {
        $formulario = Doctrine::getTable('Formulario')->find($formulario_id);

        if ($formulario->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar este formulario.';
            exit;
        }

        $data['formulario'] = $formulario;

        return view('backend.forms.ajax_editar', $data);
    }

    public function editar_form(Request $request, $formulario_id)
    {
        $formulario = Doctrine::getTable('Formulario')->find($formulario_id);

        if ($formulario->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar este formulario.';
            exit;
        }

        $request->validate(['nombre' => 'required']);

        $formulario->nombre = $request->input('nombre');
        $formulario->descripcion = $request->input('descripcion');
        $formulario->save();

        event(new HistorialModificacionEvent(
            'Se editó el formulario: [id: '.$formulario->id.']',
            $formulario->proceso_id
        ));

        return response()->json(['validacion' => true, 'redirect' => route('backend.forms.edit', [$formulario->id])]);
    }

    public function ajax_editar_campo($campo_id)
    {
        $campo = Doctrine::getTable('Campo')->find($campo_id);

        if ($campo->Formulario->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar este campo.';
            exit;
        }

        $data['edit'] = TRUE;
        $data['campo'] = $campo;
        $data['formulario'] = $campo->Formulario;

        return view('backend.forms.ajax_editar_campo', $data);
    }

    public function editar_campo_form(Request $request, $campo_id = NULL)
    {
        $campo = NULL;

        if ($campo_id) {
            $campo = Doctrine::getTable('Campo')->find($campo_id);
        } else {
            $formulario = Doctrine::getTable('Formulario')->find($request->input('formulario_id'));
            $campo = \Campo::factory($request->input('tipo'));
            $campo->formulario_id = $formulario->id;
            $campo->posicion = 1 + $formulario->getUltimaPosicionCampo();
        }

        if ($campo->Formulario->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar este campo.';
            exit;
        }

        $request->validate([
            'nombre' => 'required',
            'etiqueta' => 'required',
            //'validacion' => 'callback_clean_validacion',
        ]);

        if (!$campo_id) {
            $request->validate([
                'formulario_id' => ['required', new CheckPermissionForm],
                'tipo' => 'required',
            ]);
        }

        $campo->backendExtraValidate($request);

        $campo->nombre = trim($request->input('nombre'));
        $campo->etiqueta = $request->input('etiqueta', false);
        $campo->readonly = $request->has('readonly') && !is_null($request->input('readonly')) ? $request->input('readonly') : 0;
        $campo->valor_default = $request->has('valor_default') && !is_null($request->input('valor_default')) ? $request->input('valor_default', false) : '';
        $campo->ayuda = $request->has('ayuda') && !is_null($request->input('ayuda')) ? $request->input('ayuda') : '';
        $campo->validacion = explode('|', $request->input('validacion'));
        $campo->dependiente_tipo = $request->input('dependiente_tipo');

        // Si es checkbox, agregar corchetes al final
        $campo_dependiente = Doctrine_Query::create()
            ->from("Campo c, c.Formulario f")
            ->where("c.nombre = ?", $request->input('dependiente_campo'))
            ->andWhere("f.proceso_id = ?", $campo->Formulario->Proceso->id)
            ->fetchOne();
        $campo->dependiente_campo = $campo_dependiente && $campo_dependiente->tipo == 'checkbox' ? $request->input('dependiente_campo') . '[]' : $request->input('dependiente_campo');

        $campo->dependiente_valor = $request->input('dependiente_valor');
        $campo->dependiente_relacion = $request->input('dependiente_relacion');
        if($request->has('datos'))
            $campo->datos = $request->input('datos');
        else
            $campo->datos = $this->procesar_datos_csv($request->input('file_carga_masiva'));
        
        $campo->documento_id = $request->input('documento_id');
        $campo->extra = $request->input('extra');
        $campo->agenda_campo = $request->input('agenda_campo');
        $campo->condiciones_extra_visible = $request->has('condiciones') ? json_encode($request->input('condiciones')) : NULL;
        $campo->save();

        event(new HistorialModificacionEvent(
            'Se ha creado o modificado un campo para el formulario: [id: '.$campo->Formulario->id.']',
            $campo->Formulario->Proceso->id
        ));

        return response()->json(['validacion' => true, 'redirect' => route('backend.forms.edit', [$campo->Formulario->id])]);
    }

    public function ajax_agregar_campo($formulario_id, $tipo)
    {
        Log::info('ajax_agregar_campo ($formulario_id [' . $formulario_id . '], $tipo [' . $tipo . '])');

        $formulario = Doctrine::getTable('Formulario')->find($formulario_id);

        if ($formulario->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para agregar campos a este formulario.';
            exit;
        }

        $campo = \Campo::factory($tipo);
        $campo->formulario_id = $formulario_id;

        $data['edit'] = false;
        $data['formulario'] = $formulario;
        $data['campo'] = $campo;

        return view('backend.forms.ajax_editar_campo', $data);
    }

    public function deleteField($campo_id)
    {
        $campo = Doctrine::getTable('Campo')->find($campo_id);

        if ($campo->Formulario->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para eliminar este campo.';
            exit;
        }

        $formulario = $campo->Formulario;
        $campo->delete();

        event(new HistorialModificacionEvent(
            'Se eliminó un campo del formulario: [campo id: '.$campo->id.']',
            $campo->Formulario->Proceso->id
        ));

        return redirect()->route('backend.forms.edit', [$formulario->id]);
    }

    public function editar_posicion_campos(Request $request, $formulario_id)
    {
        $formulario = Doctrine::getTable('Formulario')->find($formulario_id);

        if ($formulario->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar este formulario.';
            exit;
        }

        $json = $request->input('posiciones');
        $formulario->updatePosicionesCamposFromJSON($json);
    }

    function clean_validacion($validacion)
    {
        return preg_replace('/\|\s*$/', '', $validacion);
    }

    public function exportar($formulario_id)
    {
        $formulario = Doctrine::getTable('Formulario')->find($formulario_id);
        $json = $formulario->exportComplete();
        header("Content-Disposition: attachment; filename=\"" . mb_convert_case(str_replace(' ', '-', $formulario->nombre), MB_CASE_LOWER) . ".simple\"");
        header('Content-Type: application/json');

        echo $json;

        event(new HistorialModificacionEvent(
            'Se exportó el formulario [id: '.$formulario->id.']',
            $formulario->proceso_id
        ));
    }

    public function import(Request $request)
    {
        try {
            $file_path = $_FILES['archivo']['tmp_name'];
            $proceso_id = $request->input('proceso_id');
            if ($file_path && $proceso_id) {
                $input = file_get_contents($_FILES['archivo']['tmp_name']);
                $formulario = \Formulario::importComplete($input, $proceso_id);
                $formulario->proceso_id = $proceso_id;
                $formulario->save();

                event(new HistorialModificacionEvent(
                    'Se importó un formulario [id: '.$formulario->id.']',
                    $formulario->proceso_id
                ));
            } else {
                die('No se especificó archivo o ID proceso');
            }
        } catch (Exception $ex) {
            die('Código: ' . $ex->getCode() . ' Mensaje: ' . $ex->getMessage());
        }
        return redirect($_SERVER['HTTP_REFERER']);
    }

    public function listarPertenece()
    {
        $data = array();
        $q = Doctrine_Query::create()
            ->select("id,nombres, apellido_paterno, apellido_materno,email")
            ->from("Usuario")
            ->where("registrado = ? AND open_id = ? AND cuenta_id=?", array(1, 0, Auth::user()->cuenta_id));
        $usuarios = $q->execute();
        $data[] = array('id' => 0, 'nombre' => 'Seleccione');

        foreach ($usuarios as $usuario) {
            $nombre_completo = $usuario->nombres;
            $trimAP = trim($usuario->apellido_paterno);
            $trimAM = trim($usuario->apellido_materno);
            $nombre_completo = (!empty($trimAP)) ? $nombre_completo . ' ' . $usuario->apellido_paterno : $nombre_completo;
            $nombre_completo = (!empty($trimAM)) ? $nombre_completo . ' ' . $usuario->apellido_materno : $nombre_completo;
            $data[] = array('id' => $usuario->id, 'nombre' => $nombre_completo, 'tipo' => 0, 'email' => $usuario->email);
        }

        $q = Doctrine_Query::create()
            ->select("id,nombre")
            ->from("GrupoUsuarios")
            ->where("cuenta_id = ?", Auth::user()->cuenta_id);
        $grupo_usuarios = $q->execute();

        foreach ($grupo_usuarios as $grupo) {
            $data[] = array('id' => $grupo->id, 'nombre' => $grupo->nombre, 'tipo' => 1, 'email' => 'grupo@grupo.com');
        }

        $items = array('items' => $data);
        $arr = array('code' => 200, 'mensaje' => 'Ok', 'resultado' => $items);
        echo json_encode($arr);
    }

    public function obtener_agenda()
    {
        $idagenda = (isset($_GET['idagenda']) && is_numeric($_GET['idagenda'])) ? $_GET['idagenda'] : 0;
        $agenda = new \App\Http\Controllers\AppointmentController();
        $agenda->ajax_obtener_agenda($idagenda);
    }

    public function ajax_mi_calendario()
    {
        $owner = (isset($_GET['pertenece']) && is_numeric($_GET['pertenece'])) ? $_GET['pertenece'] : 0;
        $agenda = new \App\Http\Controllers\AppointmentController();
        $agenda->ajax_mi_calendario($owner);
    }

    private function procesar_datos_csv($file_carga_masiva = null){
        if(is_null($file_carga_masiva))
            return null;
        $path = public_path('uploads/tmp/').$file_carga_masiva;
        $array_final = array();
        if(($handle = fopen($path, "r")) !== FALSE){
            while(($data = fgetcsv($handle, 1000, ";")) !== FALSE){
                $num = count($data);
                $associativeArray = array();
                $associativeArray['etiqueta'] = $data[0];
                $associativeArray['valor'] = $data[1];
                array_push($array_final, $associativeArray);
            }
            fclose($handle);
        }
        if (file_exists($path))
            unlink($path);
        return $array_final;
    }

    public function existeCampoEnForm(Request $request){
        $campo_nombre = $request->has('campo_nombre') ? $request->input('campo_nombre') : FALSE;
        $paso_id = $request->has('paso_id') ? $request->input('paso_id') : FALSE;
        $paso = Paso::find( $paso_id );
        
        if ($campo_nombre === FALSE || $paso_id === FALSE || $paso === FALSE){
            $response = ['mensaje' => 'Error', 'resultado' => FALSE, 'mensaje' => "El campo @@{$campo_nombre}<br>no existe"];
            return response()->json($response);
        }
        
        $form_id = $paso->formulario_id;
        $campo_nombre = str_replace('@', '', $campo_nombre);
        $q = Doctrine_Query::create()
            ->select("id")
            ->from("Campo")
            ->where("nombre = ?", $campo_nombre)->andWhere('formulario_id = ?', $form_id);
        
        $campos = $q->execute()->toArray();
        
        if(empty($campos)){
            $response = ['resultado' => FALSE, 'mensaje' => "El campo @@{$campo_nombre}<br>no existe"];
        }else{
            $response = ['resultado' => TRUE, 'mensaje' => 'Si existe'];
        }
        
        return response()->json($response);
    }
}
