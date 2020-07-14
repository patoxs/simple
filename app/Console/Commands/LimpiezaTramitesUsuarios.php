<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\Doctrine;
use Doctrine_Query;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Tramite;

class LimpiezaTramitesUsuarios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simple:limpieza';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpieza de tŕamites sin modificarse y usuarios no registrados sin actividad';

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
        //Limpia los tramites que que llevan mas de 1 dia sin modificarse, sin avanzar de etapa y sin datos ingresados (En blanco).
        \Log::info('Inicio ejecución comando limpieza: '.Carbon::now());
        $this->info('Inicio ejecución comando limpieza: '.Carbon::now());
        $amount = 10000; // cantidad de registros a operar
        $etapas_left = true; // partimos asumiendo que se encuentran registros
        $count = 0; // cuenta cuantas veces iteramos en el while
        $total_data_count = 0; // total de dato_seguimiento borrados
        while ($etapas_left) {
            $offset = $count * $amount; // parte en cero, aumenta de 10.000 en 10.000
            $etapas = DB::table('etapa')
                    ->select('etapa.tramite_id')
                    ->leftJoin('tramite', 'etapa.tramite_id', '=', 'tramite.id')
                    ->leftJoin('dato_seguimiento', 'dato_seguimiento.etapa_id', '=', 'etapa.id')
                    ->whereRaw('tramite.updated_at < DATE_SUB(NOW(),INTERVAL 1 DAY)')
                    ->where('tramite.pendiente',1)
                    ->groupBy('etapa.id')
                    ->havingRaw('COUNT(etapa.id) = 1 AND COUNT(dato_seguimiento.id) = 0')
                    ->skip($offset)
                    ->take($amount)
                    ->orderBy('etapa.id', 'DESC')
                    ->get()->toArray();            
            if (count($etapas) > 0){
                    $etapas = json_decode(json_encode($etapas), true);
                    $data_count = DB::table('tramite')->whereIn('id', $etapas)->count();
                    $eliminados = DB::table('tramite')->whereIn('id', $etapas)->delete();
                    \Log::info("Registros eliminados de tramite: ".$data_count);
                    $this->info("Registros eliminados de tramite: ".$data_count);
                    $total_data_count += $data_count;
                    $count++;
            }else{
                $etapas_left = false; // no encontramos más registros, nos permite salir del while
            }
        }
        \Log::info("Total trámites eliminados: ".$total_data_count);
        $this->info("Total trámites eliminados: ".$total_data_count);

        //Elimina los usuarios no registrados con mas de 1 dia de antiguedad y que no hayan iniciado etapas
        $etapas_left = true; // partimos asumiendo que se encuentran registros
        $count = 0; // cuenta cuantas veces iteramos en el while
        $amount = 10000; // cantidad de registros a operar
        $total_usuarios_no_registrados = 0;
        while ($etapas_left){
            $offset = $count * $amount; // parte en cero, aumenta de 10.000 en 10.000
            $usuarios = DB::table('usuario')
                ->select('usuario.id')
                ->leftJoin('etapa', 'etapa.usuario_id', '=', 'usuario.id')
                ->whereRaw('usuario.registrado=0 AND DATEDIFF(NOW(),usuario.updated_at) >= 1')
                ->groupBy('usuario.id')
                ->havingRaw('COUNT(etapa.id) = 0')
                ->skip($offset)
                ->take($amount)
                ->get()->toArray();
            if (count($usuarios) > 0){
                $usuarios = json_decode(json_encode($usuarios), true);
                $data_count = DB::table('usuario')->whereIn('id', $usuarios)->count();
                $eliminados = DB::table('usuario')->whereIn('id', $usuarios)->delete();
                \Log::info("Registros eliminados de usuario: ".$data_count);
                $this->info("Registros eliminados de usuario: ".$data_count);
                $total_usuarios_no_registrados += $data_count;
                $count++;
            }else{
                $etapas_left = false; // no encontramos más registros, nos permite salir del while
            }
        }
        \Log::info("Total registros eliminados de usuario: ".$total_usuarios_no_registrados);
        $this->info("Total registros eliminados de usuario: ".$total_usuarios_no_registrados);
        \Log::info('Fin ejecución comando limpieza: '.Carbon::now());
        $this->info('Fin ejecución comando limpieza: '.Carbon::now());
    }
}