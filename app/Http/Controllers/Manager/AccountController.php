<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use Connect_services;
use Doctrine_Query;
use Doctrine_Manager;
use Cuenta;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index()
    {
        $cuentas= Doctrine::getTable('Cuenta')->findAll();
        foreach ($cuentas as $c) {
            $id_cuenta = $c->id;

      Log::debug('ID Cuenta: ' . $id_cuenta);
        $procesos = Doctrine_Query::create()
                ->from('Proceso p, p.Cuenta c')
                ->select('COUNT(p.id) as num_procesos')
                ->whereIn('c.id', $id_cuenta)
                ->groupBy('c.id')
                ->execute();
               /* ->getSQLQuery();
                dd($procesos);*/
        Log::debug('Listando los procesos: ' . $procesos);
        
        $proceso_cuenta = DB::table('proceso')
                    ->select('proceso.id as proces')           
                    ->join('cuenta','proceso.cuenta_id', '=','cuenta.id')
                    ->where('proceso.activo', '=','1')
                    ->where('cuenta.id','=' ,$id_cuenta)
                   // ->groupBy('proceso.cuenta_id')
                    ->count(DB::raw('proceso.id'));
                   // ->get();
                 Log::info("Proceso por cuentas es : " . $proceso_cuenta);

      //  dd(proceso_cuenta);

        }
        $procesos_activos = Doctrine_Query::create()
                ->from('Proceso p, p.Cuenta c')
                ->select('p.id')
                ->where('p.activo=1')
                ->groupBy('p.id')
                ->execute();

        $data['proceso_cuenta'] = $proceso_cuenta;        
        $data['procesos_activos'] = count($procesos_activos);
        $data['procesos'] = count($procesos);
        $data['cuentas'] = $cuentas;
        $data['title'] = 'Cuentas';
        $data['content'] = view('manager.account.index', $data);



        return view('layouts.manager.app', $data);
    }

    public function edit($cuenta_id = null)
    {

        if ($cuenta_id) {
            $cuenta = Doctrine::getTable('Cuenta')->find($cuenta_id);
            $service = new Connect_services();
            $service->setCuenta($cuenta_id);
            $service->load_data();
            $calendar = $service;

            if ($cuenta != NULL && $cuenta->vinculo_produccion != null && strlen($cuenta->vinculo_produccion) > 0) {
                Log::debug('if: $cuenta->vinculo_produccion: ' . $cuenta->vinculo_produccion);
                $data['cuentas_productivas'] = $this->getListCuentasProductivas($cuenta_id, $cuenta->vinculo_produccion);
            } else {
                Log::debug('else: ' . $cuenta_id);
                $data['cuentas_productivas'] = $this->getListCuentasProductivas($cuenta_id);
            }
        } else {
            $data['cuentas_productivas'] = $this->getListCuentasProductivas();
            $cuenta = new Cuenta();
            $calendar = new Connect_services();
        }
        
        $data['seo_tags'] = \Cuenta::seo_tags($cuenta->id);
        $data['cuenta'] = $cuenta;
        $data['calendar'] = $calendar;
        $data['title'] = $cuenta->id ? 'Editar' : 'Crear';
        $data['content'] = view('manager.account.edit', $data);

        return view('layouts.manager.app', $data);
    }

    public function edit_form(Request $request, $cuenta_id = null)
    {
        Doctrine_Manager::connection()->beginTransaction();

        try {

            if ($cuenta_id)
                $cuenta = Doctrine::getTable('Cuenta')->find($cuenta_id);
            else
                $cuenta = new Cuenta();

            $validations = [
                'nombre' => 'required',
                'nombre_largo' => 'required',
                //'appkey' => 'required'
                'domain' => 'required',
                //'estilo' => 'required',
                'header' => 'required',
                'footer' => 'required',
             ];

            $messages = [
                'nombre.required' => 'El campo Nombre es obligatorio',
                'nombre_largo.required' => 'El campo Nombre largo es obligatorio',
                'domain.required' => 'El campo Dominio es obligatorio',
                'appkey.required' => 'El campo Clave App es obligatorio',
                'estilo.required' => 'Debe seleccionar un estilo',
                'header.required' => 'Debe seleccionar un header',
                'footer.required' => 'Debe seleccionar un footer'
            ];

            //$this->form_validation->set_rules('nombre', 'Nombre', 'required|url_title');

            if ($request->has('desarrollo') && $request->input('desarrollo') == 'on') {
                $validations['vinculo_produccion'] = 'required';
                $messages['vinculo_produccion.required'] = 'El campo Vinculo Producción es obligatorio';
            }

            $request->validate($validations, $messages);

            $respuesta = new \stdClass();
            $cuenta->nombre = $request->input('nombre');
            $cuenta->nombre_largo = $request->has('nombre_largo') && !is_null($request->input('nombre_largo')) ? $request->input('nombre_largo') : '';
            $cuenta->analytics = $request->has('analytics') && !is_null($request->input('analytics')) ? $request->input('analytics') : ''; //analytics
            $cuenta->mensaje = $request->has('mensaje') && !is_null($request->input('mensaje')) ? $request->input('mensaje') : '';
            $cuenta->entidad = $request->has('entidad') && !is_null($request->input('entidad')) ? $request->input('entidad') : '';
            //$cuenta->estilo = $request->input('estilo');
            $cuenta->header = $request->input('header');
            $cuenta->footer = $request->input('footer');
            $cuenta->seo_tags = $request->has('seo_tags') && !is_null($request->input('seo_tags')) ? $request->input('seo_tags') : NULL;
            $cuenta->seo_tags = json_encode($cuenta->seo_tags, JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
            if ($request->input('desarrollo') == 'on') {
                $cuenta->ambiente = 'dev';
                $cuenta->vinculo_produccion = $request->input('vinculo_produccion');
                $stmn = Doctrine_Manager::getInstance()->connection();
                $sql_desvinculo_produccion = "UPDATE cuenta SET vinculo_produccion = NULL, ambiente='prod' WHERE vinculo_produccion = " . $cuenta_id;
                $result = $stmn->prepare($sql_desvinculo_produccion);
                $result->execute();
            } else {
                $cuenta->ambiente = 'prod';
                $cuenta->vinculo_produccion = NULL;
            }

            $cuenta->client_id = $request->has('client_id') && !is_null($request->input('client_id')) ? $request->input('client_id') : NULL;
            $cuenta->client_secret = $request->has('client_secret') && !is_null($request->input('client_secret')) ? $request->input('client_secret') : NULL;
            $cuenta->entidad = $request->has('entidad') && !is_null($request->input('entidad')) ? $request->input('entidad') : '';

            $cuenta->logo = $request->input('logo');
            $cuenta->logof = $request->input('logof');
            $cuenta->save();
            $cuenta_id = (int)$cuenta->id;

            if ($cuenta_id > 0) {
                if ($cuenta->ambiente == 'dev') {
                    $stmn = Doctrine_Manager::getInstance()->connection();
                    $sql_desvinculo_produccion = "UPDATE cuenta SET vinculo_produccion = NULL, ambiente='prod' WHERE vinculo_produccion = " . $cuenta_id;
                    $result = $stmn->prepare($sql_desvinculo_produccion);
                    $result->execute();
                }

                // Calendar
                $service = new Connect_services();
                $service->setAppkey(env('AGENDA_APP_KEY'));
                $service->setDomain($request->input('domain'));
                $service->setCuenta($cuenta_id);
                $service->save();

                Doctrine_Manager::connection()->commit();

                $request->session()->flash('success', 'Cuenta guardada con éxito.');
                $respuesta->validacion = true;
                $respuesta->redirect = url('manager/cuentas');

            } else {
                $respuesta->validacion = false;
                $respuesta->errores = '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>Ocurrió un error al guardar los datos.</div>';
                Doctrine_Manager::connection()->rollback();
            }
        } catch (Exception $ex) {
            $respuesta->validacion = false;
            $respuesta->errores = '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>' . $ex->getMessage() . '</div>';
            Doctrine_Manager::connection()->rollback();
        }

        return response()->json($respuesta);
    }

    public function delete(Request $request, $cuenta_id)
    {
        $cuenta = Doctrine::getTable('Cuenta')->find($cuenta_id);
        $cuenta->delete();

        $request->session()->flash('success', 'Cuenta eliminada con éxito.');
        return redirect('manager/cuentas');
    }

    // Método que obtiene todas las cuentas productivas a las cuales se puede asociar la cuenta $idcuenta
    private function getListCuentasProductivas($cuenta_id = null, $vinculo_prod = null)
    {

        Log::debug('getListCuentasProductivas(' . $cuenta_id . ')');

        $stmn = Doctrine_Manager::getInstance()->connection();

        if ($cuenta_id != null && $vinculo_prod != null) {
            $sql_vinculo_produccion = "SELECT id, nombre FROM cuenta WHERE ambiente = 'prod' AND id != " . $cuenta_id . " AND id NOT IN (SELECT vinculo_produccion FROM cuenta WHERE vinculo_produccion IS NOT NULL AND vinculo_produccion != " . $vinculo_prod . ")";
        } else if ($cuenta_id != null && $vinculo_prod == null) {
            $sql_vinculo_produccion = "SELECT id, nombre FROM cuenta WHERE ambiente = 'prod' AND id != " . $cuenta_id . " AND id NOT IN (SELECT vinculo_produccion FROM cuenta WHERE vinculo_produccion IS NOT NULL)";
        } else {
            $sql_vinculo_produccion = "SELECT id, nombre FROM cuenta WHERE ambiente = 'prod' AND id NOT IN (SELECT vinculo_produccion FROM cuenta WHERE vinculo_produccion IS NOT NULL)";
        }

        $result = $stmn->prepare($sql_vinculo_produccion);
        $result->execute();
        $vinculo_prod = $result->fetchAll();

        Log::debug('vinculo_produccion OK [' . json_encode($vinculo_prod) . ']');

        return $vinculo_prod;
    }
}
