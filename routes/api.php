<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PeticioneController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


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

Route::controller(\App\Http\Controllers\PeticioneController::class)->group(function () {
    Route::get('peticiones', 'index');
    Route::get('mispeticiones', 'listmine');
    Route::get('peticiones/firmadas', 'listarFirmadas');
    Route::get('peticiones/{id}', 'show');
    Route::delete('peticiones/{id}', 'delete');
    Route::put('peticiones/firmar/{id}', 'firmar');
    Route::put('peticiones/{id}', 'update');
    Route::put('peticiones/estado/{id}', 'cambiarEstado');
    Route::post('peticiones', 'store');
});
Route::controller(AuthController::class)->group(function () {
Route::post('login', 'login');
Route::post('register', 'register');
Route::post('logout', 'logout');
Route::post('refresh', 'refresh');
Route::get('me', 'me');
});

Route::controller(CategoriaController::class)->group(function () {
    Route::post('categorias', 'store');
    Route::get('categorias/show/{id}', 'show')->middleware('auth:api');
    Route::get("categorias" ,"list");
    Route::get('/categorias', [CategoriaController::class, 'index']);

});
