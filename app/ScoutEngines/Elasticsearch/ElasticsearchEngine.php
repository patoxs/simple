<?php

namespace App\ScoutEngines\Elasticsearch;

use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\Builder;

class ElasticsearchEngine extends \ScoutEngines\Elasticsearch\ElasticsearchEngine
{

    /**
     * Perform the given search on the engine.
     *
     * @param  Builder $builder
     * @param  array $options
     * @return mixed
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        $params = [
            'index' => $this->index,
            'type' => $builder->index ?: $builder->model->searchableAs(),
            /*'body' => [
                'query' => [
                    'multi_match' => [
                        //'query' => "*{$builder->query}*",
                        'query' => $query,
                    ],
                ]
            ]*/
        ];


        //Si $builder->query, es por que traera opciones adicionales, de no ser así, se trata como un multi_match solamente
        if (is_array($builder->query)) {
            
            $query = array_get($builder->query, 'query');
            $filter = array_get($builder->query, 'filter', '');
            $params['body']['query']['bool']['should'] = [
                'multi_match' => [
                    'query' => $query,
                    'fields' => ["id", "etapas.dato_seguimientos.nombre", "etapas.dato_seguimientos.valor" ],
                ],
            ];

            //si queremos filtrar por varios terminos, basta con agregr 'terms' y no 'term',
            //lo siguiente sera un array asociativo donde su llave es la columna y el valor son todos los posibles filtros
            if (!empty($filter)) {
                // $params['body']['query']['bool']['filter']['terms'] = $filter;
            }
        } else {
            $params['body']['query'] = [
                'multi_match' => [
                    'query' => $builder->query,
                    'fields' => ["id", "etapas.dato_seguimientos.nombre", "etapas.dato_seguimientos.valor" ],
                ],
            ];

        }

        if ($sort = $this->sort($builder)) {
            $params['body']['sort'] = $sort;
        }
        if (isset($options['from'])) {
            $params['body']['from'] = $options['from'];
        }
        if (isset($options['size'])) {
            $params['body']['size'] = $options['size'];
        }
        if (isset($options['numericFilters']) && count($options['numericFilters'])) {
            $params['body']['query']['bool']['filter'] = $options['numericFilters'];
            //$params['body']['query']['bool']['must'] = array_merge($params['body']['query']['bool']['must'],
            //$options['numericFilters']);
        }

        try{
            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', env('ELASTICSEARCH_HOST'));
        }catch (\Exception $ex) {
            \Log::error('Error en conexión a elasticsearch: '. $ex->getMessage());
            return false;
        }
        
        return $this->elastic->search($params);
    }


    /**
     * Map the given results to instances of the given model.
     *
     * @param  mixed $results
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return Collection
     */
    public function map($results, $model)
    {
        if(!$results){
            return false;
        }

        if ($results['hits']['total'] == 0) {
            return Collection::make();
        }

        $keys = collect($results['hits']['hits'])
            ->pluck('_id')->values()->all();
        $models = $model->whereIn(
            $model->getKeyName(), $keys
        )->get()->keyBy($model->getKeyName());

        return collect($results['hits']['hits'])->map(function ($hit) use ($model, $models) {

            $result = isset($models[$hit['_id']]) ? $models[$hit['_id']] : null;
            //$result->highlight = isset($hit['highlight']) ? $hit['highlight'] : null;
            return $result;
        })->filter()->values();
    }

}