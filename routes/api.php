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

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'api/v1',
    'as' => 'structured-data.',
    'middleware' => config('structureddata.api.middleware.base')
], function () {
    Route::group([
        'middleware' => config('structureddata.api.middleware.auth')
    ], function () {
        
        // ITEMS ...
        
        // Bind item
        Route::bind(config('structureddata.api.routes.load-item'), function($id) {
            return (config('structureddata.modelsNamespace') . '\Item')::findOrFail($id);
        });
            
        // Items manipulation
        Route::apiResource(config('structureddata.api.routes.load-item'), config('structureddata.controllersNamespace') 
            . '\ItemController');
        
        // Load the items for a reference node
        Route::get(config('structureddata.api.routes.validate-item') . '/{item}', [
            'as' => 'validate',
            'uses' => config('structureddata.controllersNamespace') . '\ItemController@validation']);
        
        // ITEM VALUES ...
        
        // Bind item value
        Route::bind(config('structureddata.api.routes.load-value'), function($id) {
            return (config('structureddata.modelsNamespace') . '\Value')::findOrFail($id);
        });
            
        // Item values manipulation
        Route::apiResource(config('structureddata.api.routes.load-value'), config('structureddata.controllersNamespace') 
            . '\ValueController');
        
        // NODES ...
        
        // Load the items for a reference node
        Route::get(config('structureddata.api.routes.load-node') . '/{reference}', [
            'as' => 'load-node',
            'uses' => config('structureddata.controllersNamespace') . '\NodeController@load']);
        
        // Load the nodes for an item
        Route::get(config('structureddata.api.routes.load-item-nodes') . '/{reference}', [
            'as' => 'load-node',
            'uses' => config('structureddata.controllersNamespace') . '\ItemController@loadNodes']);
        
        // SCHEMAS ...
        
        // Bind schema
        Route::bind(config('structureddata.api.routes.schema'), function($id) {
            return (config('structureddata.modelsNamespace') . '\Schema')::findOrFail($id);
        });
        
        // Schemas manipulation
        Route::apiResource(config('structureddata.api.routes.schema'), config('structureddata.controllersNamespace') . '\SchemaController');
        
        // Schemas importer command
        Route::get(config('structureddata.api.routes.schemas-import'), function() {
            return Artisan::call('schemas:import', [
                'url' => Request::get('url')
            ]);
        });
        
        // PROPERTIES ...
        
        // Bind schema property
        Route::bind(config('structureddata.api.routes.property-schema'), function($id) {
            return (config('structureddata.modelsNamespace') . '\PropertySchema')::findOrFail($id);
        });
        
        // Schema properties manipulation
        Route::apiResource(config('structureddata.api.routes.property-schema'), config('structureddata.controllersNamespace') 
            . '\PropertySchemaController');
        
        // PROPERTY TYPES ...
        
        // Bind available type for a property
        Route::bind(config('structureddata.api.routes.available-type'), function($id) {
            return (config('structureddata.modelsNamespace') . '\AvailableType')::findOrFail($id);
        });
        
        // Available types manipulation
        Route::apiResource(config('structureddata.api.routes.available-type'), config('structureddata.controllersNamespace')
            . '\AvailableTypeController');
    });
});
