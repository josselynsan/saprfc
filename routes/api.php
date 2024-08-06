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

Route::post('v1/saprfc/avisos/consulta', [SapController::class, 'ZSGDEA_CONSULTA_AVISOS']);
Route::get('v1/saprfc/personal/habilitado/{iCentroCosto}', [SapController::class, 'ZSGDEA_PERSONAL_HABILITADO']);
Route::get('v1/saprfc/cuenta-contrato/detalles/{cuentaContrato}', [SapController::class, 'ZSGDEA_DETALLES_CTA_CONTRATO']);
Route::get('v1/saprfc/aviso/detalles/{iNumero}', [SapController::class, 'ZSGDEA_DETALLE_AVISO']);
Route::get('v1/saprfc/medidas/{iFechaIni}/{iFechaFin}', [SapController::class, 'ZSGDEA_CONSULTA_MEDIDAS']);
Route::get('v1/saprfc/interlocutor/detalles', [SapController::class, 'ZPM_DETALLE_INTERLOCUTOR']);
Route::post('v1/saprfc/zona-grupo-planifica/consulta', [SapController::class, 'Z_WM_FIND_ZONA_GRUPO_PLANIFICA']);
Route::post('v1/saprfc/solicitudes/consulta', [SapController::class, 'ZSGDEA_CONSULTA_SOLICITUDES']);

