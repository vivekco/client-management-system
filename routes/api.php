<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DuplicateController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/clients', [ClientController::class, 'index']);
Route::post('/clients/import', [ImportController::class, 'import']);
Route::get('/clients/export', [ClientController::class, 'export']);

Route::get('/duplicate-groups', [DuplicateController::class, 'index']);
Route::get('/duplicate-groups/{id}', [DuplicateController::class, 'show']);

Route::get('/imports/logs', [ImportController::class, 'logs']);
Route::get('/imports/summaries', [ImportController::class, 'summaries']);