<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/prepareDiag', [\App\Http\Controllers\PrepareDataController::class, 'prepareDiag']);
Route::get('/prepareICD10', [\App\Http\Controllers\PrepareDataController::class, 'prepareICD10']);


Route::get('/unionData', [\App\Http\Controllers\DataPrepareController::class, 'unionData']);
Route::get('/joinDataLogic', [\App\Http\Controllers\DataPrepareController::class, 'joinDataLogic']);
