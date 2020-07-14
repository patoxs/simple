<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\Doctrine;
use Doctrine_Query;
use Regla;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simple:sendmails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notificación de etapas por vencer';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $etapas = DB::table('etapa')
                ->select('etapa.*','tarea.vencimiento_notificar_email','tarea.vencimiento_notificar_dias','tarea.vencimiento_habiles','tarea.nombre as tarea_nombre')
                ->leftJoin('tarea', 'tarea.id', '=', 'etapa.tarea_id')
                ->where('etapa.pendiente',1)
                ->where('tarea.vencimiento_notificar',1)
                ->get();
        if(count($etapas)==0){
            \Log::error("No existen etapas que notificar");
            $this->info("No existen etapas que notificar");
        }else{
            $notificaciones_enviadas = 0;
            foreach ($etapas as $e){
                $vencimiento=$e->vencimiento_at;
                if(!is_null($vencimiento)){
                    
                    $dias_por_vencer=ceil((strtotime($e->vencimiento_at)-time())/60/60/24);
                    $dias_no_habiles = 0;
                    if ($e->vencimiento_habiles == 1)
                        $dias_no_habiles = (new \App\Helpers\dateHelper())->get_working_days_count(date('Y-m-d'), $e->vencimiento_at);
                    
                    try{
                        $regla=new Regla($e->vencimiento_notificar_email);
                        $email=$regla->getExpresionParaOutputConsole($e->id);
                        $email = str_replace('"','',$email);
                    }catch(Exception $ex){
                        $this->info('Se produjo una excepción al obtener el correo--'.$ex);
                        $email = NULL;
                    }
                    
                    if ($dias_por_vencer > 0)
                        $dias_por_vencer-=$dias_no_habiles;                 
                    
                    if ($dias_por_vencer <= $e->vencimiento_notificar_dias && !is_null($email)){
                        $cuenta = DB::table('cuenta')
                                ->select('cuenta.nombre as cuenta_nombre','cuenta.nombre_largo as cuenta_nombre_largo','proceso.nombre as proceso_nombre')
                                ->leftJoin('proceso', 'proceso.cuenta_id', '=', 'cuenta.id')
                                ->leftJoin('tramite', 'tramite.proceso_id', '=', 'proceso.id')
                                ->where('tramite.id',$e->tramite_id)
                                ->first();
                        $data_usuario = DB::table('usuario')
                                        ->where('id',$e->usuario_id)
                                        ->first();
                        $this->info('Enviando correo de notificacion para tramite: ' . $e->tramite_id);
                        $subject = 'Etapa se encuentra ' . ($dias_por_vencer>0 ?'por vencer':'vencida');
                        
                        $url_final = empty(env('APP_MAIN_DOMAIN')) ? url("/etapas/ejecutar/{$e->id}") : "https://".$cuenta->cuenta_nombre.".".env('APP_MAIN_DOMAIN')."/etapas/ejecutar/{$e->id}";
                        $message = '<p>La etapa "' . $e->tarea_nombre . '" del proceso "'.$cuenta->proceso_nombre.'" se encuentra '
                                .($dias_por_vencer>0?'a '.$dias_por_vencer. (abs($dias_por_vencer)==1?' día ':' días ') .($e->vencimiento_habiles == 1 ? 'habiles ' : '') .
                                        'por vencer':('vencida '.($dias_por_vencer<0 ? 'hace '.abs($dias_por_vencer).(abs($dias_por_vencer)==1?' día ':' días ') : 'hoy'))).' ('.date('d/m/Y',strtotime($e->vencimiento_at)).').' . "</p><br>" . 
                                '<p>Usuario asignado: ' . $data_usuario->usuario .'</p>'.($dias_por_vencer > 0 ? '<p>Para realizar la etapa, hacer click en el siguiente link: '. $url_final .'</p>':'');
                        try{
                            $notificaciones_enviadas++;
                            \Mail::send('emails.send', ['content' => $message], function ($message) use ($e, $subject, $cuenta, $email) {
                                $message->subject($subject);
                                $mail_from = env('MAIL_FROM_ADDRESS');
                                if(empty($mail_from))
                                    $message->from($cuenta->cuenta_nombre . '@' . env('APP_MAIN_DOMAIN', 'localhost'), $cuenta->cuenta_nombre_largo);
                                else
                                    $message->from($mail_from);
                                
                                if(empty(env('EMAIL_TEST')))
                                    $message->to($email);
                                else{
                                    $destinatarios_test = explode(",",env('EMAIL_TEST'));
                                    $message->to($destinatarios_test);
                                }
                                    
                            });
                        }catch(\Exception $e){
                            $notificaciones_enviadas--;
                            \Log::error("Error al notificar etapa en cron: " . $e);
                            $this->info("Error al notificar etapa en cron: " . $e);
                        }
                    }
                }
            }
            \Log::error("Notificaciones de etapas por vencer enviadas: " . $notificaciones_enviadas);
            $this->info("Notificaciones de etapas por vencer enviadas: " . $notificaciones_enviadas);
        }
    }

    
}
