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
Route::namespace('Backend')->group(function () {
    //Tramites
    Route::get('/tramites/{tramite_id?}', 'ApiController@tramites');
    //Procesos
    Route::get('/procesos/{proceso_id?}/{recurso?}', 'ApiController@procesos');
    Route::post('/notificar/{tramite_id}', 'ApiController@notificar');
    Route::post('/estados/{tramite_id}', 'ApiController@estados');
    Route::post('/progress/{tramite_id}', 'ApiController@progress');
});