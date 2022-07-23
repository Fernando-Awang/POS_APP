<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Backend\BarangController;
use App\Http\Controllers\Backend\KategoriBarangController;
use App\Http\Controllers\Backend\PelangganController;
use App\Http\Controllers\Backend\SupplierController;
use App\Http\Controllers\Backend\UserController;
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
Route::get('unauthorize', function(){
    return responseJson(false, 'Unauthenticated. u need token', null, 401);
})->name('login');
Route::post('login', [AuthController::class, 'login'])->name('auth');
Route::middleware('auth:sanctum')->group(function(){
    Route::resource('kategori-barang', KategoriBarangController::class)->except('create', 'edit');
    Route::resource('barang', BarangController::class)->except('create', 'edit');
    Route::resource('supplier', SupplierController::class)->except('create', 'edit');
    Route::resource('pelanggan', PelangganController::class)->except('create', 'edit');
    Route::resource('user', UserController::class)->except('create', 'edit');
});

