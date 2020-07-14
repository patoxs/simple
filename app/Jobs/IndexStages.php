<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;
use App\Models\Tramite;

class IndexStages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tramite_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tramite_id)
    {
        $this->tramite_id=$tramite_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $tramite = Tramite::find($this->tramite_id);
            if(!is_null($tramite)){
                $tramite->save();
                $tramite->searchable();
            }
        }catch(\Exception $e){
            \Log::error('Exception rabbitmq elasticsearch: '.$e->getMessage());
        }
    }
}
