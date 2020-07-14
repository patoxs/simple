<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\Doctrine;
use App\Models\AuditoriaOperaciones;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuditController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $order = $request->input('order');
        $direction = $request->input('direction');
        $per_page = 25;

        $query = AuditoriaOperaciones::select('id', 'fecha', 'motivo', 'operacion', 'usuario', 'proceso')
            ->whereCuentaId(Auth::user()->cuenta_id);

        if ($order && $direction)
            $query = $query->orderBy($order, $direction);
        else
            $query = $query->orderBy('id','DESC');

        $query = $query->paginate($per_page);

        $data['registros'] = $query;
        $data['order'] = $order;
        $data['direction'] = $direction;

        return view('backend.audit.index', $data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function view($id)
    {
        $registro_auditoria = Doctrine::getTable("AuditoriaOperaciones")->find($id);
        $registro_auditoria->detalles = json_decode($registro_auditoria->detalles, true);

        if ($registro_auditoria->operacion == 'Retroceso a Etapa' ||
            $registro_auditoria->operacion == 'Cambio de Fecha de Vencimiento') {
            /* Compatibilidad con auditorias registradas antes de esta version */
            if (!isset($registro_auditoria->detalles['etapa'])) {
                $detalles['tramite'] = $registro_auditoria->detalles['tramite'];
                $detalles['etapa'] = $registro_auditoria->detalles;
                $detalles['tarea'] = $detalles['etapa']['tarea'];
                $detalles['usuario'] = $detalles['etapa']['usuario'];
                if ($registro_auditoria->operacion == 'Retroceso a Etapa') {
                    $detalles['datos_seguimiento'] = $detalles['etapa']['datos_seguimiento'];
                    unset($detalles['etapa']['datos_seguimiento']);
                }
                unset($detalles['etapa']['tramite']);
                unset($detalles['etapa']['tarea']);
                unset($detalles['etapa']['usuario']);
                unset($detalles['etapa']['proceso']);

                $registro_auditoria->detalles = $detalles;
            }
        }

        $data['registro'] = $registro_auditoria;
        $data['title'] = 'Auditoría de ' . $registro_auditoria->operacion . ' en fecha ' . $registro_auditoria->fecha;

        return view('backend.audit.view', $data);
    }
}
