<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Helpers\Doctrine;
use Doctrine_Query;
use Carbon\Carbon;

class BorrarDataSeguimiento extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simple:borrar_data {cuenta_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permite eliminar la data de los trámites de una cuenta en particular';

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
        $cuenta_id = $this->argument('cuenta_id');
        $this->info('Inicio ejecución: '.Carbon::now());
        $amount = 10000; // cantidad de registros a operar
        $etapas_left = true; // partimos asumiendo que se encuentran registros
        $count = 0; // cuenta cuantas veces iteramos en el while
        $total_data_count = 0; // total de dato_seguimiento borrados
        while ($etapas_left) {
            $offset = $count * $amount; // parte en cero, aumenta de 10.000 en 10.000
            $etapas = DB::table('etapa')
                    ->select('etapa.id')
                    ->leftJoin('tramite', 'etapa.tramite_id', '=', 'tramite.id')
                    ->leftJoin('proceso', 'tramite.proceso_id', '=', 'proceso.id')
                    ->where('proceso.cuenta_id',$cuenta_id)
                    ->skip($offset) // skip es como el offset pero cuando va junto con take()
                    ->take($amount)
                    ->orderBy('etapa.id', 'DESC')
                    ->get()->toArray();
           if (count($etapas) > 0){
                $etapas = json_decode(json_encode($etapas), true);
                $data_count = DB::table('dato_seguimiento')->whereIn('etapa_id', $etapas)->count();
                $eliminados = DB::table('dato_seguimiento')->whereIn('etapa_id', $etapas)->delete();
                $this->info("Registros eliminados de dato_seguimiento: ".$data_count);
                $total_data_count += $data_count;
                $count++;
            } else {
                $etapas_left = false; // no encontramos más registros, nos permite salir del while
            }
        }
        $this->info("Total registros eliminados de dato_seguimiento: ".$total_data_count);
        $this->info('Fin ejecución: '.Carbon::now());
    }
}
