<?php

use App\Http\Controllers\CacheController;
use App\Http\Controllers\FrequencyController;
use App\Http\Controllers\ScheduleController;
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

Route::get('/schedule/{endTime}/{startTime?}', ScheduleController::class);

Route::get('/cache/{cache_key}', CacheController::class);

Route::post('/frequency/{id}', FrequencyController::class);
