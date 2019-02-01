<?php

use Illuminate\Http\Request;

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

Route::post('/webhook/apple/{applicationID}', ['uses' => 'API\WebhookController@apple']);

Route::post('/verified-receipt', ['uses' => 'API\SubscriptionsController@verifiedReceipt']);

Route::post('/set-appsflyer-data', ['uses' => 'API\SetDataController@appsflyer']);

Route::post("/application/add", ['uses' => 'API\ApplicationController@add']);
