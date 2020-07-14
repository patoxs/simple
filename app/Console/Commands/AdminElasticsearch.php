<?php

namespace App\Console\Commands;

use App\Suggestion;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;

class AdminElasticsearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:admin {operation} {model?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea los indices en elasticsearch';

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
        ini_set('memory_limit', -1);

        $client = new Client();

        $operation = $this->argument('operation');
        $model = $this->argument('model');

        if ($operation == 'create') {
            try {
                $this->info('Se borra índice');
                $response = $client->request('DELETE', env('ELASTICSEARCH_HOST') . '/' . env('ELASTICSEARCH_INDEX'));
            } catch (ClientException $e) {
                $this->line($e->getMessage());
            }

            $this->info('Se crea índice');
            $response = $client->request('PUT', env('ELASTICSEARCH_HOST') . '/' . env('ELASTICSEARCH_INDEX'), [
                'json' => [
                    'mappings' => [
                        'proceso' => [
                            'properties' => [
                                'query' => [
                                    'type' => 'completion'
                                ]
                            ]
                        ],
                        'tramite' => [
                            'properties' => [
                                'id' => [
                                    'type' => 'keyword',
                                ],
                                'proceso_id' => [
                                    'type' => 'integer'
                                ],
                                'created_at' => [
                                    'type' => 'date',
                                    'format' => 'yyyy-MM-dd HH:mm:ss'
                                    //'fielddata' => true
                                ],
                                'updated_at' => [
                                    'type' => 'date',
                                    'format' => 'yyyy-MM-dd HH:mm:ss'
                                    //'fielddata' => true
                                ],
                                'ended_at' => [
                                    'type' => 'date',
                                    'format' => 'yyyy-MM-dd HH:mm:ss'
                                    //'fielddata' => true
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
        } elseif ($operation == 'index') {
            if (!$model || $model == 'tramite') {
                $this->call('scout:import', ['model' => 'App\Models\Tramite']);
            }

            if (!$model || $model == 'proceso') {
                $this->call('scout:import', ['model' => 'App\Models\Proceso']);
            }

        }


    }
}