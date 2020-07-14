<?php

namespace App\Http\Controllers\Manager;

use App\Helpers\Doctrine;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Doctrine_Query;
use Doctrine_Core;
use Illuminate\Support\Facades\DB;
use App\Models\Cuenta;

class StatisticsController extends Controller
{
    public function index()
    {
        return redirect()->route('manager.statistics.accounts');
    }

    public function accounts($cuenta_id = null, $proceso_id = null)
    {
        if (!$cuenta_id) 
        {

            $cuentas = Cuenta::join('proceso', 'proceso.cuenta_id', '=', 'cuenta.id')
                        ->join('tramite', 'tramite.proceso_id', '=', 'proceso.id')
                        ->join('etapa', 'etapa.tramite_id', '=', 'tramite.id')
                        ->select(DB::raw('cuenta.id as id, cuenta.nombre as cuenta_nombre, sum(case when tramite.updated_at > DATE_SUB(NOW(),INTERVAL 30 DAY) then 1 else 0 end) as cantidad_tramites'))
                        ->groupBy('cuenta.id')
                        ->havingRaw('COUNT(etapa.id)>0')
                        ->orderBy('cuenta.nombre')
                        ->get(); 

            $data['cuentas'] = $cuentas;
            $data['title'] = 'Cuentas';
            $data['content'] = view('manager.statistics.accounts', $data);

        }else{
            $cuenta = Doctrine::getTable('Cuenta')->find($cuenta_id);
            $tramites = Cuenta::join('proceso', 'proceso.cuenta_id', '=', 'cuenta.id')
                ->leftJoin('tramite', 'tramite.proceso_id', '=', 'proceso.id')
                ->leftJoin('etapa', 'etapa.tramite_id', '=', 'tramite.id')
                ->select(DB::raw('proceso.id as id, proceso.nombre as proceso_nombre, sum(case when tramite.updated_at > DATE_SUB(NOW(),INTERVAL 30 DAY) then 1 else 0 end) as cantidad_tramites'))
                ->where('cuenta.id',$cuenta_id)
                ->where('proceso.activo',1)
                ->groupBy('proceso.id')
                ->havingRaw('COUNT(etapa.id)>=0')
                ->orderBy('proceso.nombre')
                ->get();

            $data['tramites'] = $tramites;
            $data['title'] = $cuenta->nombre;
            $data['cuenta'] = $cuenta;
            $data['content'] = view('manager.statistics.process', $data);
        }
        // } else {
        //     $data['proceso'] = Doctrine::getTable('Proceso')->find($proceso_id);

        //     $tramites = Doctrine_Query::create()
        //         ->from('Tramite t, t.Proceso p, t.Etapas e, e.DatosSeguimiento d')
        //         ->where('p.activo=1 AND p.id = ?', $proceso_id)
        //         ->andWhere('t.updated_at > DATE_SUB(NOW(),INTERVAL 30 DAY)')
        //         ->orderBy('t.updated_at DESC')
        //         ->having('COUNT(e.id) > 0')
        //         ->groupBy('t.id')
        //         ->execute();

        //     $data['tramites'] = $tramites;

        //     $data['title'] = $data['proceso']->nombre;

        //     $data['content'] = view('manager.statistics.procedures', $data);
        // }

        return view('layouts.manager.app', $data);
    }

}