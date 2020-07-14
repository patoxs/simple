<?php

namespace App\Listeners;

use App\Models\HistorialModificacion;
use App\Models\Proceso;
use App\Models\UsuarioBackend;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class RegistraModificacionListener
{
    private $user;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->user = auth()->user();
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if (isset($this->user->user_type) && $this->user->user_type == 'backend') {

            try {
                $proceso = Proceso::where('id', $event->proceso_id);
            } catch(Exception $e) {
                Log::info('Error al registrar una modificacion de Flujo', [
                    'error' => $e
                ]);
            }

            if ($proceso) {
                try {
                    $record = new HistorialModificacion();
                    $record->description = $event->description;
                    $record->created_at = Carbon::now();
                    $record->usuario_id = $this->user->id;
                    $record->proceso_id = $event->proceso_id;
                    $record->save();

                    Log::info('=== Se ha registrado una modificacion de Flujo  ========>', [
                        'usuario' => $this->user->email,
                        'entidad' => $event->description
                    ]);

                } catch(Exception $e) {
                    Log::info('Error al registrar una modificacion de Flujo', [
                        'error' => $e
                    ]);
                }
            }
        }
    }
}
