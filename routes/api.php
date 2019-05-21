<?php

/*
 |--------------------------------------------------------------------------
 | API Routes
 |--------------------------------------------------------------------------
 |
 | Here is where you can register API routes for your application. These
 | routes are loaded by the RouteServiceProvider within a group which
 | is assigned the "api" middleware group. Enjoy building your API!
 |
 */

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'api/v1',
    'as' => 'linked-data.',
    'middleware' => config('structureddata.api.middleware.base')
], function () {
    Route::group([
        'middleware' => config('structureddata.api.middleware.auth')
    ], function () {
        
        // Load a single entity by ID
        Route::get(config('structureddata.api.routes.load-entity') . '/{entity}', [
            'as' => 'load-entity', 
            'uses' => config('structureddata.controllersNamespace') . '\EntityController@load']);
        
        // Load the entities for a reference node
        Route::get(config('structureddata.api.routes.load-node') . '/{reference}', [
            'as' => 'load-node',
            'uses' => config('structureddata.controllersNamespace') . '\NodeController@load']);
        
        // Load the nodes for an entity
        Route::get(config('structureddata.api.routes.load-entity-nodes') . '/{reference}', [
            'as' => 'load-node',
            'uses' => config('structureddata.controllersNamespace') . '\EntityController@loadNodes']);
    });
});
