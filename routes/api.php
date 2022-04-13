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

// Note - this uses the throttle middleware to limit the number of requests to 10 per minute (based on incoming IP).
Route::get('/help', [\App\Http\Controllers\OutgoingWebhookController::class, 'store'])->name('api.help')->middleware('throttle:10,1');
