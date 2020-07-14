<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Backend API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Api
Route::namespace('Integration')->group(function () {
    //Lista de catálogos
    Route::get('/especificacion/procesos', 'SpecificationController@procesos_get');
    //Integración
    Route::get('especificacion/servicio/proceso/{id_proceso}/tarea/{id_tarea}', 'SpecificationController@servicio_get');
    //Fomularios
    Route::get('especificacion/formularios/proceso/{id_proceso}/{id_tarea?}/{id_paso?}', 'SpecificationController@formularios_get');

    //Iniciar un proceso
    Route::post('api/tramites/proceso/{id_proceso}/tarea/{id_tarea}', 'ApiController@tramites_post');
    //Continuar un proceso
    Route::post('api/tramites/tramite/{id_tramite}/etapa/{id_etapa}/paso/{id_paso}', 'ApiController@tramites_put');
});