<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Cuenta;
use Illuminate\Support\Facades\Mail;

class ScheduleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        #if user not logged, create new user and auto login this new user.
        //$this->middleware('auth_user');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        if(env('TOKEN_CRON') !== $request->input('token')){
            \Log::info('No tiene permisos para ejecutar el cron');
            return 'No tiene permisos para ejecutar el cron';
            exit;
        }
        \Log::info('####INICIO CRON');
        $limpieza = \Artisan::call('simple:limpieza');
        // $etapas_avanzadas = \Artisan::call('simple:avanzar');
        // $emails_enviados = \Artisan::call('simple:sendmails');
        $message = 'Se ha ejecutado el cron de limpieza, avance de etapas vencidas y notificación de etapas en '.env('APP_MAIN_DOMAIN', 'localhost');    
        $cuenta = \Cuenta::cuentaSegunDominio();
        $to = env('DESTINATARIOS_CRON');
        $subject = 'cron de tareas';
        $destinatarios = explode(",",$to);
        if(!empty($to)){
            foreach($destinatarios as $destinatario){
                $this->envio_correo($destinatario,$message,$subject,$cuenta);
            }
        }
        \Log::info('####FIN CRON');
        return $message;
    }

    private function envio_correo($destinatario,$message,$subject,$cuenta){
        try{
            Mail::send('emails.send', ['content' => $message], function ($message) use ($subject, $cuenta, $destinatario) {
                $message->subject($subject);
                $mail_from = env('MAIL_FROM_ADDRESS');
                if(empty($mail_from))
                    $message->from($cuenta->nombre . '@' . env('APP_MAIN_DOMAIN', 'localhost'), $cuenta->nombre_largo);
                else
                    $message->from($mail_from);

                $message->to($destinatario);
            });
        }catch(\Exception $e){
            Log::error("Error al notificar ejecución de cron: " . $e);
            $this->info("Error al notificar ejecución de cron: " . $e);
        }
    }
}
