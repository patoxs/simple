<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\Doctrine;
use Doctrine_Query;
use App\Models\Etapa;

class AvanzarTramitesVencidos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simple:avanzar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permite avanzar los trámites que tengan etapas vencidas';

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
        $fecha_actual = \Carbon\Carbon::now('America/Santiago')->format('Y-m-d');
        $etapas_vencidas = Etapa::where('vencimiento_at','<=',$fecha_actual)
                           ->where('pendiente',1)
                           ->get();
        foreach($etapas_vencidas as $etapa){
            $etapa = Doctrine::getTable('Etapa')->find($etapa->id);
            if($etapa->vencida())
                $etapa->avanzar();
        }
        if(count($etapas_vencidas)){
            \Log::info('etapas vencidas avanzadas--'.count($etapas_vencidas));
            $this->info('etapas vencidas avanzadas--'.count($etapas_vencidas));
        }else{
            \Log::info('No existen etapas para avanzar');
            $this->info('No existen etapas para avanzar');
        }
    }   
}
