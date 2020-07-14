<?php

namespace App\Http\Controllers\Manager;

use App\Helpers\Doctrine;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProceduresExposedController extends Controller
{

    public function index()
    {
        $data['cuentas'] = Doctrine::getTable('Cuenta')->findAll();
        $data['title'] = 'Trámites expuestos';
        $data['json'] = Doctrine::getTable('Proceso')->findProcesosExpuestos();
        $data['content'] = view('manager.procedures_exposed.index', $data);

        return view('layouts.manager.app', $data);
    }

    public function searchAccount(Request $request)
    {
        $data['cuentas'] = Doctrine::getTable('Cuenta')->findAll();
        $cuenta_id = $request->input('cuenta_id');
        $data['json'] = Doctrine::getTable('Proceso')->findProcesosExpuestos($cuenta_id);
        $data['title'] = 'Busqueda de trámites expuestos';
        $data['cuenta_sel'] = $cuenta_id;
        $data['content'] = view('manager.procedures_exposed.index', $data);

        return view('layouts.manager.app', $data);
    }
}
