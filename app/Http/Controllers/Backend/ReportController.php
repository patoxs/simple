<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use Doctrine_Query;
use App\Jobs\ProcessReport;
use App\Models\Job;
use Cuenta;

class ReportController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Doctrine_Query_Exception
     */
    public function index()
    {
        $data['procesos'] = Doctrine_Query::create()
            ->from('Proceso p, p.Cuenta c')
            ->where('c.id = ?', Auth::user()->cuenta_id)
            ->orderBy('p.nombre asc')
            ->execute();

        return view('backend.report.index', $data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Doctrine_Query_Exception
     */
    public function list($id)
    {
        $proceso = Doctrine::getTable('Proceso')->find($id);
        $reportes = Doctrine_query::create()
            ->from('Reporte r')
            ->where('r.proceso_id = ? or r.proceso_id = ?', array($id, $proceso->id))
            ->orderBy('r.id desc')->execute();

        if (!is_null(Auth::user()->procesos) && !in_array($id, explode(',', Auth::user()->procesos))) {
            echo 'Usuario no tiene permisos para ver los reportes';
            exit;
        }

        if ($proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para listar los formularios de este proceso';
            exit;
        }

        $data['proceso'] = $proceso;
        $data['reportes'] = $reportes;
        $data['rol'] = Auth::user()->rol;

        return view('backend.report.list', $data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create($id)
    {
        $proceso = Doctrine::getTable('Proceso')->find($id);

        if (!is_null(Auth::user()->procesos) && !in_array($id, explode(',', Auth::user()->procesos))) {
            echo 'Usuario no tiene permisos';
            exit;
        }

        if ($proceso->cuenta_id != Auth::user()->cuenta_id ||
            (!in_array('super', explode(",", Auth::user()->rol)) &&
                !in_array('reportes', explode(",", Auth::user()->rol))
            )
        ) {
            echo 'No tiene permisos para crear este documento';
            exit;
        }

        $data['edit'] = false;
        $data['proceso'] = $proceso;
        $data['reporte'] = null;

        return view('backend.report.edit', $data);
    }

    /**
     * @param Request $request
     * @param bool $reporte_id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function store(Request $request, $reporte_id = false)
    {
        if ($reporte_id) {
            $reporte = Doctrine::getTable('Reporte')->find($reporte_id);
        } else {
            $reporte = new \Reporte();
            $proceso_id = $request->input('proceso_id');
            $reporte->proceso_id = $proceso_id;
        }

        if (!is_null(Auth::user()->procesos) &&
            !in_array($reporte->Proceso->id, explode(',', Auth::user()->procesos))) {
            echo 'Usuario no tiene permisos';
            exit;
        }

        if ($reporte->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para editar este documento.';
            exit;
        }

        $this->validate($request, [
            'nombre' => 'required',
            'campos' => 'required'
        ]);

        $reporte->nombre = $request->input('nombre');
        $reporte->campos = $request->input('campos');
        $reporte->save();

        return response()->json([
            'validacion' => true,
            'redirect' => route('backend.report.list', [$reporte->Proceso->id])
        ]);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $reporte = Doctrine::getTable('Reporte')->find($id);

        if (!is_null(Auth::user()->procesos) && !in_array($reporte->Proceso->id, explode(',', Auth::user()->procesos))) {
            echo 'Usuario no tiene permisos';
            exit;
        }

        if ($reporte->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'No tiene permisos para editar este documento';
            exit;
        }

        $data['reporte'] = $reporte;
        $data['edit'] = true;
        $data['proceso'] = $reporte->Proceso;
        $data['title'] = 'Edición de Reporte';

        return view('backend.report.edit', $data);
    }

    /**
     * @param Request $request
     * @param $reporte_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function view(Request $request, $reporte_id)
    {

        $running_jobs = Job::where('user_id', Auth::user()->id)
                               ->whereIn('status', [Job::$running])
                               ->where('user_type', Auth::user()->user_type)
                               ->count();
        if($running_jobs >= env('DOWNLOADS_MAX_JOBS_PER_USER', 1)){
            $request->session()->flash('error', 
                "Ya tiene un reporte en ejecución pendiente, por favor espere a que termine.");
            return redirect()->back();
        }

        $reporte = Doctrine::getTable('Reporte')->find($reporte_id);

        if (!is_null(Auth::user()->procesos) &&
            !in_array($reporte->Proceso->id, explode(',', Auth::user()->procesos))) {
            echo 'Usuario no tiene permisos para ver el reporte';
            exit;
        }

        if ($reporte->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos';
            exit;
        }

        // Reporte del proceso
        $proceso_reporte = $reporte->Proceso;
        $proceso = $proceso_reporte->findIdProcesoActivo($proceso_reporte->id, $reporte->Proceso->cuenta_id);

        $tramites_completos = 0;
        $tramites_vencidos = 0;
        $tramites_pendientes = 0;
        $etapas_cantidad = 0;
        $suma_promedio_tramite = 0;
        $num_tramites = 0;

        // Parametros
        $query = $request->input('query');
        $created_at_desde = $request->has('created_at_desde') ? $request->input('created_at_desde') : null;
        $created_at_hasta = $request->has('created_at_hasta') ? $request->input('created_at_hasta') : null;
        $pendiente = $request->has('pendiente') && is_numeric($request->input('pendiente')) ? $request->input('pendiente') : -1;
        $formato = $request->input('formato');
        $filtro = $request->input('filtro');
        $per_page = 50;
        $page = $request->input('page', 1); // Get the ?page=1 from the url
        $offset = ($page * $per_page) - $per_page;
        $params = array();

        Log::debug("Explorando proceso id: " . $proceso->id);

        if ($created_at_desde) {
            array_push($params, 'created_at >= ' . "'" . date('Y-m-d', strtotime($created_at_desde)) . "'");
        }
        if ($created_at_hasta) {
            array_push($params, 'created_at <= ' . "'" . date('Y-m-d', strtotime($created_at_hasta)) . "'");
        }
        if ($pendiente != -1) {
            array_push($params, 'pendiente = ' . $pendiente);
        }

        Log::debug("Explorando query: " . $query);

        if ($query) {
            $result = Proceso::search($query)->get();
            if (array_key_exists('total', $result) && $result['total'] > 0) {
                $matches = array_keys($result['matches']);
                Log::debug('$matches: ' . $matches);
                array_push($params, 't.id IN (' . implode(',', $matches) . ')');
            } else {
                $params = array('0');
            }
        }

        //$reporte_tabla = $reporte->getReporteAsMatrix($params);

        if ($formato == "pdf") {

            foreach ($proceso->Tramites as $tramite) {
                $etapas_cantidad = Doctrine_query::create()->from('Etapa e')->where('e.tramite_id = ?', $tramite->id)->count();

                if ($tramite->pendiente == 0) {
                    $tramites_completos++;
                } else if ($etapas_cantidad > 0) {
                    if ($tramite->getTareasVencidas()->count() > 0) {
                        $tramites_vencidos++;
                    }

                    $tramites_pendientes++;
                }
            }

            $promedio_tramite = $proceso->getDiasPorTramitesAvg();
            $promedio_tramite = $promedio_tramite[0]['avg'];

            $suma_promedio_tramite += $promedio_tramite;
            $num_tramites++;

            $promedio_tramite = ($num_tramites <= 0) ? 0 : $suma_promedio_tramite / $num_tramites;

            $data['tramites_vencidos'] = $tramites_vencidos;
            $data['tramites_pendientes'] = $tramites_pendientes;
            $data['tramites_completos'] = $tramites_completos;
            $data['promedio_tramite'] = $promedio_tramite;
            $data['reporte'] = $reporte_tabla;
            $data['title'] = $reporte->nombre . ' - Proceso "' . $reporte->Proceso->nombre . '"';

            $pdf = PDF::loadView('backend.report.pdf', $data)->setPaper('a4', 'landscape');
            return $pdf->download('reporte.pdf');
        } else if ($formato == "xls") {
            $http_host = request()->getSchemeAndHttpHost();
            $email_to = Auth::user()->email;
            $name_to = Auth::user()->nombres;
            $email_subject = '¡Tu reporte está listo! ';

            $reporte_tabla = $reporte->getArregloInicial();
            $header_variables = $reporte->getHeaderVariables();
            $reportname = $reporte->nombre;//Nombre del reporte
            $cuenta = Cuenta::cuentaSegunDominio();
            $nombre_cuenta = Cuenta::cuentaSegunDominio()->nombre_largo; //Nombre de la cuenta
            $name_user = Doctrine::getTable('Usuario')->find(Auth::user()->id);
            Log::debug("nombre reporte " . $reporte->nombre);
            Log::debug('El nombre de la cuenta es'. $nombre_cuenta);
            $this->dispatch(new ProcessReport(Auth::user()->id, Auth::user()->user_type, $proceso->id, $reporte->id, $params, $reporte_tabla, $header_variables,$http_host,$email_to,$name_to,$email_subject,$created_at_desde,$created_at_hasta,$pendiente,$cuenta, $nombre_cuenta, $reportname, $reporte->nombre));

            $request->session()->flash('success', "Se enviará un enlace para la descarga de los documentos una vez est&eacute; listo a la direcci&oacute;n: ".$email_to);
            return redirect()->back();
        }

        Log::debug("cantidad reporte matriz");
        $ntramites = count($reporte->getReporteAsMatrix($params)) - 1;

        Log::debug("cantidad trámites: " . $ntramites);
        $reporte_tabla = $reporte->getReporteAsMatrix($params, $per_page, $offset);

        Log::debug("reporte tabla: " . json_encode($reporte_tabla));

        //Paginamos
        $reporte_tabla = new LengthAwarePaginator(
            $reporte_tabla, // Only grab the items we need
            $ntramites, // Total items
            $per_page, // Items per page
            $page, // Current page
            ['path' => $request->url(), 'query' => $request->query()] // We need this so we can keep all old query parameters from the url
        );

        $data['tramites_vencidos'] = $tramites_vencidos;
        $data['tramites_pendientes'] = $tramites_pendientes;
        $data['tramites_completos'] = $tramites_completos;
        $data['promedio_tramite'] = $promedio_tramite;
        $data['filtro'] = $filtro;
        $data['query'] = $query;
        $data['reporte_tabla'] = $reporte_tabla;
        $data['reporte'] = $reporte;
        $data['pendiente'] = $pendiente;
        $data['created_at_desde'] = $created_at_desde;
        $data['created_at_hasta'] = $created_at_hasta;
        $data['title'] = $reporte->nombre . ' - Proceso "' . $proceso_activo->nombre . '"';

        return view('backend.report.view', $data);
    }

    /**
     * @param $reporte_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($reporte_id)
    {
        $reporte = Doctrine::getTable('Reporte')->find($reporte_id);

        if (!is_null(Auth::user()->procesos) && !in_array($reporte->Proceso->id, explode(',', Auth::user()->procesos))) {
            echo 'Usuario no tiene permisos';
            exit;
        }

        if ($reporte->Proceso->cuenta_id != Auth::user()->cuenta_id) {
            echo 'Usuario no tiene permisos para eliminar este documento.';
            exit;
        }

        $proceso = $reporte->Proceso;
        $reporte->delete();

        return redirect()->route('backend.report.list', [$proceso->id]);
    }

    public function descargar_archivo(Request $request, $user_id, $job_id, $file_name){
        if (!Cuenta::cuentaSegunDominio()->descarga_masiva) {
            $request->session()->flash('error', 'Servicio no tiene permisos para descargar.');
            return redirect()->back();
        }

        if (Auth::user()->id != $user_id) {
            $request->session()->flash('error', 'Usuario no tiene permisos para descargar.');
            return redirect()->back();
        }

        // validar que user_id y job_id sean enteros

        $job_info = Job::where('user_id', Auth::user()->id)
                        ->where('id', $job_id)
                        ->where('filename', $file_name)->first();
        
        $full_path = $job_info->filepath.DIRECTORY_SEPARATOR.$job_info->filename;
        if(file_exists($full_path)){
            $job_info->downloads += 1;
            $job_info->save();
            return response()->download($full_path)->deleteFileAfterSend(true);
        }else{
            abort(404);
        }
    }


}
