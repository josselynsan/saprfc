<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\SapController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('v1/test/conexion', [SapController::class, 'index']);
Route::post('v1/test/llamar', [SapController::class, 'callRfcFunction']);
Route::post('v1/saprfc', [SapController::class, 'saprfc']);

Route::post('v1/saprfc/CONSULTA_AVISOS', [SapController::class, 'ZSGDEA_CONSULTA_AVISOS']);
Route::post('v1/saprfc/PERSONAL_HABILITADO', [SapController::class, 'ZSGDEA_PERSONAL_HABILITADO']);
Route::post('v1/saprfc/DETALLES_CTA_CONTRATO', [SapController::class, 'ZSGDEA_DETALLES_CTA_CONTRATO']);
Route::post('v1/saprfc/DETALLE_AVISO', [SapController::class, 'ZSGDEA_DETALLE_AVISO']);
Route::post('v1/saprfc/CONSULTA_MEDIDAS', [SapController::class, 'ZSGDEA_CONSULTA_MEDIDAS']);
Route::post('v1/saprfc/DETALLE_INTERLOCUTOR', [SapController::class, 'ZPM_DETALLE_INTERLOCUTOR']);
Route::post('v1/saprfc/FIND_ZONA_GRUPO_PLANIFICA', [SapController::class, 'Z_WM_FIND_ZONA_GRUPO_PLANIFICA']);

