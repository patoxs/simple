<?php

namespace App\Http\Controllers\Manager;


use App\Helpers\Doctrine;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Anuncio;
use Doctrine_Manager;
use App\Models\UsuarioBackend;
use Illuminate\Support\Facades\DB;
use App\Models\Job;
use Illuminate\Database\Eloquent\SoftDeletes;
class StatusReportController extends Controller
{
    public function index(){
        $data['reportes'] = DB::table('jobs') //Lista los reportes solicitados con su estado
                    ->select('jobs.id',
                        'jobs.extra as nombre_reporte',
                        'usuario_backend.email as solicitante',
                        'usuario_backend.rol',
                        'jobs.status',
                        'jobs.created_at'
                    )
                    ->join('usuario_backend','usuario_backend.id', '=','jobs.user_id')
                    ->orderBy('jobs.id', 'desc')
                    ->get();

        $data['title'] = 'Estado Reportes';
        $data['content'] = view('manager.reportes.index', $data);

        return view('layouts.manager.app', $data);
    }
   

    /**
     * @param Request $request
     * @param $report_id
     */
     public function delete(Request $request, $reporte_id){
        $anuncio = Job::find($reporte_id);
        $anuncio->delete();

        $request->session()->flash('success', 'Registro de estado de reporte eliminado con éxito.');
        return redirect('manager/reportes');
    }
}
