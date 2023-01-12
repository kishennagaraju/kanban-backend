<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => 'verify_custom_token'], function () {
    Route::get('list-cards', [App\Http\Controllers\Api\Tasks\TasksController::class, 'list']);
    Route::post('board', [App\Http\Controllers\Api\Tasks\TasksController::class, 'save']);

    Route::get('export-db', [App\Http\Controllers\Api\Tasks\TasksController::class, 'exportDb']);
});
