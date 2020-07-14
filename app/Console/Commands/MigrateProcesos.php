<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cuenta;
use App\Models\Proceso;
use App\Models\Tramite;
use Illuminate\Support\Facades\DB;

class MigrateProcesos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simple:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migración de procesos archivados en versiones anteriores a la versión pública actual (solo para eliminación de versión)';

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
        $cuentas_prod = DB::table('cuenta')->where('ambiente','prod')->get();
        $actualizados = 0;
        foreach($cuentas_prod as $cuenta_prod){
            $procesos_publicos = DB::table('proceso')->where('estado','public')->where('cuenta_id',$cuenta_prod->id)->get();
            foreach($procesos_publicos as $proc_public){
                $procesos_archivados = DB::table('proceso')->where('estado','arch')->where('root',$proc_public->root)->get();
                if(count($procesos_archivados)){
                    $this->info("Proceso ".$proc_public->id);
                    foreach($procesos_archivados as $proc_arch){
                        DB::table('tramite')->where('proceso_id',$proc_arch->id)->update(array('proceso_id' => $proc_public->id));
                        DB::table('proceso')->where('id',$proc_arch->id)->delete();
                        $actualizados++;
                    }
                }
            }
        }
        if($actualizados>0)
            $this->info("Procesos migrados exitósamente");
        else
            $this->info("Procesos actualizados");
    }       
}
