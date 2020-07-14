<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Job;
use App\Models\Reporte;
use Cuenta;
use App\Helpers\Doctrine;
use Doctrine_Query;
use DB;
use Carbon\Carbon;

class ProcessReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $user_type;
    protected $proceso_id;
    protected $reporte_id;
    protected $params;
    protected $max_running_jobs = 1;
    protected $tries = 1;
    protected $job_info;
    protected $reporte_tabla;
    protected $header_variables;
    protected $link_host;
    protected $email_to;
    protected $email_subject;
    protected $email_message;
    protected $email_name;
    protected $_base_dir;
    protected $nombre_reporte;
    protected $desde;
    protected $hasta;
    protected $pendiente;
    protected $cuenta;
    protected $reportname;
    protected $nombre_cuenta;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id,$user_type,$proceso_id,$reporte_id,$params,$reporte_tabla,$header_variables,$host, $email_to, $email_name, $email_subject, $desde, $hasta, $pendiente, $cuenta, $reportname, $nombre_cuenta){
        $this->user_id = $user_id;
        $this->user_type = $user_type;
        $this->proceso_id = $proceso_id;
        $this->reporte_id = $reporte_id;
        $this->params = $params;
        $this->reporte_tabla = $reporte_tabla;
        $this->header_variables = $header_variables;
        $this->link_host = $host;
        $this->email_to = $email_to;
        $this->email_name = $email_name;
        $this->email_subject = $email_subject;
        $this->_base_dir = public_path('uploads/tmp');
        if(!file_exists($this->_base_dir) ) {
            mkdir($this->_base_dir, 0777, true);
        }
        $this->desde = $desde;
        $this->hasta = $hasta;
        $this->pendiente = $pendiente;
        $this->cuenta = $cuenta;
        $this->reportname = $reportname;
        $this->nombre_cuenta = $nombre_cuenta;
  
        $this->job_info = new Job();
        $this->arguments = serialize([$user_id, $user_type, $proceso_id, $reporte_id]);
        $this->job_info->user_id = $this->user_id;
        $this->job_info->user_type = $this->user_type;
        $this->job_info->arguments = $this->arguments;
        $this->job_info->extra = $this->nombre_cuenta; //se guarda el nombre del reporte en el campo extra 
        $this->job_info->status = Job::$created;
        $this->job_info->save();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        $this->job_info->status = Job::$running;
        $this->job_info->save();

        $this->generar_consulta();

        $this->job_info->filename = $this->nombre_reporte.'.xls';
        $this->job_info->filepath = $this->_base_dir;
        
        try{
            $this->send_notification();
            $this->job_info->status = Job::$finished;
        }catch(\Exception $e){
            Log::error("ProcessReport::handle() Error al enviar notificacion: " . $e->getMessage());
            $this->job_info->status = Job::$error;
        }
        $this->job_info->save();
    }

    private function generar_consulta(){

        $excel_row = $this->reporte_tabla;
        $header_variables = $this->header_variables;

        $query = DB::table('tramite')
            ->join('etapa', 'tramite.id', '=', 'etapa.tramite_id')
            ->join('dato_seguimiento', 'dato_seguimiento.etapa_id', '=', 'etapa.id')
            ->join('proceso', 'proceso.id', '=', 'tramite.proceso_id')
            ->select(DB::raw('tramite.id as id, proceso.id as proceso_id, tramite.created_at as created_at, tramite.pendiente as pendiente ,tramite.created_at as created_at, tramite.updated_at as updated_at, tramite.ended_at as ended_at'))
            ->where('proceso.id',$this->proceso_id)
            ->whereNull('tramite.deleted_at');


        if(!is_null($this->desde)){
            $this->desde = $this->desde.'00:00:00';
            $this->desde = date('Y-m-d H:i:s', strtotime($this->desde));
            $desde = Carbon::createFromFormat('Y-m-d H:i:s',$this->desde);
            $query = $query->where('tramite.created_at','>=',$desde);
        }
            
        if(!is_null($this->hasta)){
            $this->hasta = $this->hasta.'23:59:59';
            $this->hasta = date('Y-m-d H:i:s', strtotime($this->hasta));
            $hasta = Carbon::createFromFormat('Y-m-d H:i:s',$this->hasta);
            $query = $query->where('tramite.created_at','<=',$hasta);
        }

        if($this->pendiente != -1)
            $query = $query->where('tramite.pendiente',$this->pendiente);

        $query = $query
            ->groupBy('tramite.id')
            ->havingRaw('COUNT(dato_seguimiento.id) > 0 OR COUNT(etapa.id) > 1')
            ->get();

        $tramites = json_decode(json_encode($query), true);

        foreach ($tramites as $t) {

            $etapas_actuales = DB::table('tramite')
                ->join('etapa', 'tramite.id', '=', 'etapa.tramite_id')
                ->join('tarea', 'tarea.id', '=', 'etapa.tarea_id')
                ->select(DB::raw('tarea.nombre as tarea_nombre'))
                ->where('tramite.id',$t['id'])
                ->where('etapa.pendiente',1)
                ->get();
            $etapas_actuales_arr = array();
            foreach ($etapas_actuales as $etapa) {
                $etapas_actuales_arr[] = $etapa->tarea_nombre;
            }
            $etapas_actuales_str = implode(',', $etapas_actuales_arr);
            $t['etapa_actual'] = $etapas_actuales_str;
            $t['pendiente'] = $t['pendiente'] ? 'En curso' : 'Completado';
            $t['created_at'] = isset($t['created_at']) ? \Carbon\Carbon::parse($t['created_at'])->format('d-m-Y H:i:s') : '';
            $t['updated_at'] = isset($t['updated_at']) ? \Carbon\Carbon::parse($t['updated_at'])->format('d-m-Y H:i:s') : '';
            $t['ended_at'] = isset($t['ended_at']) ? \Carbon\Carbon::parse($t['ended_at'])->format('d-m-Y H:i:s') : '';

            $row = array();

            $datos_actuales = DB::table('tramite')
                ->join('etapa', 'tramite.id', '=', 'etapa.tramite_id')
                ->join('dato_seguimiento', 'dato_seguimiento.etapa_id', '=', 'etapa.id')
                ->select(DB::raw('dato_seguimiento.id, MAX(dato_seguimiento.id) as max_id'))
                ->where('tramite.id',$t['id'])
                ->groupBy('dato_seguimiento.nombre')
                ->get();

            $datos_actuales_ids=array();
            foreach($datos_actuales as $d)
                $datos_actuales_ids[]=$d->max_id;

            $datos = DB::table('tramite')
                ->join('etapa', 'tramite.id', '=', 'etapa.tramite_id')
                ->join('dato_seguimiento', 'dato_seguimiento.etapa_id', '=', 'etapa.id')
                ->whereIn('dato_seguimiento.id',$datos_actuales_ids)
                ->groupBy('dato_seguimiento.nombre')
                ->get();


            foreach ($datos as $d) {
                $val = $d->valor;
                if (!is_string($val)) {
                    $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                }
                $t[$d->nombre] = strip_tags($val);
            }

            foreach ($header_variables as $h) {
                $var_find = explode("->", $h);
                if (count($var_find) > 1) {
                    $row[] = isSet($t[$var_find[0]]) ? json_decode($t[$var_find[0]])->$var_find[1] : '';
                } else {
                    $row[] = isSet($t[$h]) ? $t[$h] : '';
                }
            }
            $excel_row[] = $row;
        }

        $this->nombre_reporte = 'reporte-'.$this->reporte_id.'-'.Carbon::now('America/Santiago')->format('dmYHis');
        Excel::create($this->nombre_reporte, function ($excel) use ($excel_row) {
            $excel->sheet('reporte', function ($sheet) use ($excel_row) {
                $sheet->fromArray($excel_row, null, 'A1', false, false);
            });
        })->store('xls', $this->_base_dir);  
    }

    private function send_notification(){
        $reportname = $this->reportname;
        $nombre_cuenta = $this->nombre_cuenta;
        $link = "{$this->link_host}/backend/reportes/descargar_archivo/{$this->user_id}/{$this->job_info->id}/{$this->job_info->filename}";
        $data = ['link' => $link, 'reportname' => $reportname, 'nombre_cuenta' => $nombre_cuenta];
        $email_to = $this->email_to;
        $email_subject = $this->email_subject;
        $cuenta = $this->cuenta;
        Mail::send('emails.download_link', $data, function($message) use ($cuenta, $link, $email_to, $email_subject){

            $message->subject($email_subject);
            $mail_from = env('MAIL_FROM_ADDRESS');
            if(empty($mail_from))
                $message->from($cuenta->nombre . '@' . env('APP_MAIN_DOMAIN', 'localhost'), $cuenta->nombre_largo);
            else
                $message->from($mail_from);

            $message->to($email_to);
        });
    }

    
}
