<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\SapConnectionService;
use App\Traits\LogService;
use App\Traits\ResponseService;
use App\Utils\TransactionUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class SapController extends Controller
{

    use LogService, ResponseService;

    protected $sapService;

    public function __construct(SapConnectionService $sapService)
    {
        $this->sapService = $sapService;
    }

    public function index()
    {
        $connection = $this->sapService->getConnection();

        // Realiza tus operaciones SAP aquí

        // Cierra la conexión al final
        $this->sapService->close();

        return response()->json(['message' => 'Operación completada con éxito']);
    }


    public function callRfcFunction(Request $request)
    {

        
        $functionName = 'ZSGDEA_PERSONAL_HABILITADO';
        $parameters = [
            'I_CENTRO_COSTO' => '3431001',
            'I_FECHA_FIN' => '20240720',
        ];
        /*
        
        $functionName = 'ZSGDEA_DETALLES_CTA_CONTRATO';
        $parameters = [
            'I_CUENTAS_CONTRATO' => ['11557160'],
        ];

        $functionName = 'ZSGDEA_DETALLE_AVISO'; ok
        $parameters = [
            'I_QMNUM' => '11557160',
        ];
        //Consulta medidas de personal
        $functionName = 'ZSGDEA_CONSULTA_MEDIDAS'; ok
        $parameters = [
            'I_FECHA_INI' => '',
            'I_FECHA_FIN' => '',
            'I_CLASES' => '',
        ];


        //Consulta de Información básica de los avisos
        $functionName = 'ZSGDEA_CONSULTA_AVISOS'; ok
        $parameters = [
            'I_FECHA_INI' => '20240101',
            'I_HORA_INI' => '010101',
            'I_FECHA_FIN' => '20240131',
            'I_HORA_FIN' => '235959',
        ];


        // PROBADO
        //Consultar detalle del interlocutor
        //CTA_CONTRATO :se debe formatear con ceros a la izq, hasta 12 caracteres
        //INTERLOCUTOR :se debe formatear con ceros a la izq, hasta 10 caracteres
        $functionName = 'ZPM_DETALLE_INTERLOCUTOR';  ok
        $parameters = [
            'CTA_CONTRATO' => '',  //11557160
            'INTERLOCUTOR' => '',  //10215971
        ];
        
        
        // NO TIENE HABILITADO CONSUMO REMOTO
        //Busqueda de agrupacion de estructura regional
        //PRIORIDAD (2)
        $functionName = 'Z_WM_FIND_ZONA_GRUPO_PLANIFICA';
        $parameters = [
            'ZCALLE' => '3',  
            'ZHOUSE1' => '2',  
            'ZQMART' => '',  
            'ZCIUDAD' => 'BOGOTA',  
        ];
        
        */

        $result = $this->sapService->callRFC($functionName, $parameters);

        // Cierra la conexión después de hacer la llamada
        $this->sapService->close();

        return response()->json($result);
    }


    public function saprfc(Request $request)
    {
        try{



            /*
            if (empty($request->input('functionName')))  return $this->statusHttp(400); 
            if (empty($request->input('parameters')))  return $this->statusHttp(400); 
            */

            $functionName = $request->input('functionName');
            //$parameters = $request->input('parameters');

            
            $parameters = [
                "I_FECHA_INI" => "20240101",
                "I_FECHA_FIN" => "20240131",
                "I_CLASES" => ["MASSN"=> "MA","MASSN"=> "MB"]
            ];

            /*
            $parameters = [
                "I_CUENTAS_CONTRATO" => ['TABLE_LINE' => '12644563', 'TABLE_LINE' => '12644562' ]
            ];

            */
            

            

            $result = $this->sapService->callRFC($functionName, $parameters);
            
            $formattedData = $result;
    
            $this->sapService->close();
    

            /*
            $oldData = trim(json_encode($formattedData->toArray($request)), '{}');

            HelperLog::logAdd(
                true,
                auth()->user()->id,
                auth()->user()->username,
                'api/sgc/tarea/v2/shows',
                YiiGetParams()['eventosLogText']['View'] . " TransactionId : " . $transactionId,
                $oldData,
                '',
                array()
            );
            */


            $timestamp = TransactionUtility::getTimestamp();
            $transactionId = TransactionUtility::createTransactionId();

            return response()->json([
                'origin'        => 'serversaprfc',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => true,
                'data'          => $formattedData,
                'message'       => 'success',
            ], 200);


        } catch (\Exception $e) {
            return $this->statusHttp(400, $e);
        }

    }


    /** PROBADO Y FUNCIONANDO */
    public function ZSGDEA_CONSULTA_AVISOS(Request $request)
    {
        try{

            $estadosArray = [];
            if (is_array($request->asttx) && !empty($request->asttx)) {
                foreach ($request->asttx as $estado) {
                    if (!empty($estado)) { // Verifica si el estado no está vacío
                        $estadosArray[] = ["ASTTX" => $estado];
                    }
                }
            }

            $cuentascontratoArray = [];
            if (is_array($request->vkont) && !empty($request->vkont)) {
                foreach ($request->vkont as $cuentacontrato) {
                    if (!empty($cuentacontrato)) { // Verifica si el estado no está vacío
                        $cuentascontratoArray[] = ["VKONT" => $cuentacontrato];
                    }
                }
            }

            $clasesArray = [];
            if (is_array($request->notifType) && !empty($request->notifType)) {
                foreach ($request->notifType as $clase) {
                    if (!empty($clase)) { // Verifica si el estado no está vacío
                        $clasesArray[] = ["NOTIF_TYPE" => $clase];
                    }
                }
            }


            //Consulta de Información básica de los avisos
            $functionName = 'ZSGDEA_CONSULTA_AVISOS';
            $parameters = [
                'I_CENTRO' => $request->iCentro, // (opcional) -> si viene vacio, no debe incluirse
                'I_FECHA_INI' => $request->iFechaIni,
                'I_HORA_INI' => $request->iHoraIni,
                'I_FECHA_FIN' => $request->iFechaFin,
                'I_HORA_FIN' => $request->iHoraFin,
                'I_GRUPO_AVISOS' => "SGDEA",
                "I_CLASES" => $clasesArray,
                "I_ESTADOS" => $estadosArray,
                "I_CUENTAS_CONTRATO" => $cuentascontratoArray

            ];
    
        
            $result = $this->sapService->callRFC($functionName, $parameters);
            
            $formattedData = $result;
    
            $this->sapService->close();
    

            $timestamp = TransactionUtility::getTimestamp();
            $transactionId = TransactionUtility::createTransactionId();

            return response()->json([
                'origin'        => 'serverSapRfc',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => true,
                'data'          => $formattedData,
                'message'       => 'success',
            ], 200);


        } catch (\Exception $e) {
            return $this->statusHttp(400, $e);
        }

    }



    /** PROBADO - PENDIENTE CONFIRMAR SI LAS TABLAS Y TODOS SUS CAMPOS SON CRITERIOS DE CONSULTAS */
    public function ZSGDEA_DETALLE_AVISO(Request $request)
    {
        try{

            
            $functionName = 'ZSGDEA_DETALLE_AVISO';
            $parameters = [
                'I_NUMERO' => $request->iNumero,
            ];

        
            $result = $this->sapService->callRFC($functionName, $parameters);
            
            $formattedData = $result;
    
            $this->sapService->close();
    

            $timestamp = TransactionUtility::getTimestamp();
            $transactionId = TransactionUtility::createTransactionId();

            return response()->json([
                'origin'        => 'serverSapRfc',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => true,
                'data'          => $formattedData,
                'message'       => 'success',
            ], 200);


        } catch (\Exception $e) {
            return $this->statusHttp(400, $e);
        }

    }





    /** PROBADO - PENDIENTE CONFIRMAR SI LAS TABLAS Y TODOS SUS CAMPOS SON CRITERIOS DE CONSULTAS */
    public function ZSGDEA_PERSONAL_HABILITADO(Request $request)
    {
        try{


            $functionName = 'ZSGDEA_PERSONAL_HABILITADO';
            $parameters = [
                'I_CENTRO_COSTO' => $request->iCentroCosto, //'3431001',
            ];
        
            $result = $this->sapService->callRFC($functionName, $parameters);
            
            $formattedData = $result;
    
            $this->sapService->close();
    

            $timestamp = TransactionUtility::getTimestamp();
            $transactionId = TransactionUtility::createTransactionId();

            return response()->json([
                'origin'        => 'serverSapRfc',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => true,
                'data'          => $formattedData,
                'message'       => 'success',
            ], 200);


        } catch (\Exception $e) {
            return $this->statusHttp(400, $e);
        }

    }


    /** PROBADO - PENDIENTE REVISAR TABLE_LINE, NO LO TOMA COMO CAMPO VALIDO */
    public function ZSGDEA_DETALLES_CTA_CONTRATO(Request $request)
    {
        try{


            $cuentascontratoArray = [];
            if (is_array($request->tableLine) && !empty($request->tableLine)) {
                foreach ($request->tableLine as $cuentacontrato) {
                    if (!empty($cuentacontrato)) { // Verifica si el estado no está vacío
                        $cuentascontratoArray[] = ["TABLE_LINE" => $cuentacontrato];
                    }
                }
            }

            
            $functionName = 'ZSGDEA_DETALLES_CTA_CONTRATO';
            $parameters = [
                "I_CUENTAS_CONTRATO" => $cuentascontratoArray,

            ];

        
            $result = $this->sapService->callRFC($functionName, $parameters);
            
            $formattedData = $result;
    
            $this->sapService->close();
    

            $timestamp = TransactionUtility::getTimestamp();
            $transactionId = TransactionUtility::createTransactionId();

            return response()->json([
                'origin'        => 'serverSapRfc',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => true,
                'data'          => $formattedData,
                'message'       => 'success',
            ], 200);


        } catch (\Exception $e) {
            return $this->statusHttp(400, $e);
        }

    }




    /** PROBADO */
    public function ZSGDEA_CONSULTA_MEDIDAS(Request $request)
    {
        try{

            $clasesArray = [];
            if (is_array($request->massn) && !empty($request->massn)) {
                foreach ($request->massn as $clase) {
                    if (!empty($clase)) { // Verifica si el estado no está vacío
                        $clasesArray[] = ["MASSN" => $clase];
                    }
                }
            }


            $functionName = 'ZSGDEA_CONSULTA_MEDIDAS';
            $parameters = [
                'I_FECHA_INI' => $request->iFechaIni,
                'I_FECHA_FIN' => $request->iFechaFin,
                "I_CLASES" => $clasesArray,

            ];

    
        
            $result = $this->sapService->callRFC($functionName, $parameters);
            
            $formattedData = $result;
    
            $this->sapService->close();
    

            $timestamp = TransactionUtility::getTimestamp();
            $transactionId = TransactionUtility::createTransactionId();

            return response()->json([
                'origin'        => 'serverSapRfc',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => true,
                'data'          => $formattedData,
                'message'       => 'success',
            ], 200);


        } catch (\Exception $e) {
            return $this->statusHttp(400, $e);
        }

    }

    /** PROBADO */
    public function ZPM_DETALLE_INTERLOCUTOR(Request $request)
    {
        try{
            $opcion = 0;
            $message = "success";
            $formattedData = [];
            $parameters = [];
            $status = true;
    
            if (!empty($request->ctaContrato)) {
                $parameters = [
                    'CTA_CONTRATO' => $request->ctaContrato,  //11557160
                ];
            } else {
                if (!empty($request->interlocutor)) {
                    $parameters = [
                        'INTERLOCUTOR' => $request->interlocutor,  //10215971
                    ];
                } else {
                    $opcion = 1;
                    $message = "error";
                    $status = false;
                }
            }
            
            if ($opcion == 0) {
                $functionName = 'ZPM_DETALLE_INTERLOCUTOR';
                $result = $this->sapService->callRFC($functionName, $parameters);
                $formattedData = $result;
                $this->sapService->close();
            }
    

            $timestamp = TransactionUtility::getTimestamp();
            $transactionId = TransactionUtility::createTransactionId();

            return response()->json([
                'origin'        => 'serverSapRfc',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => $status,
                'data'          => $formattedData,
                'message'       => $message,
            ], 200);


        } catch (\Exception $e) {
            return $this->statusHttp(400, $e);
        }

    }

    /** PROBADA - NO TIENE HABILITADO CONSUMO REMOTO */
    public function Z_WM_FIND_ZONA_GRUPO_PLANIFICA(Request $request)
    {
        try{


            
            // NO FUNCIONA LA FUNCION
            //Busqueda de agrupacion de estructura regional
            $functionName = 'Z_WM_FIND_ZONA_GRUPO_PLANIFICA';
            $parameters = [
                'ZCALLE' => $request->zCalle,  
                'ZHOUSE1' => $request->zHouse1,  
                'ZQMART' => $request->zQmart,  
                'ZCIUDAD' => $request->zCiudad,  
            ];
            
        
            $result = $this->sapService->callRFC($functionName, $parameters);
            
            $formattedData = $result;
    
            $this->sapService->close();
    

            $timestamp = TransactionUtility::getTimestamp();
            $transactionId = TransactionUtility::createTransactionId();

            return response()->json([
                'origin'        => 'server',
                'transactionId' => $transactionId,
                'timestamp'     => $timestamp,
                'status'        => true,
                'data'          => $formattedData,
                'message'       => 'success',
            ], 200);


        } catch (\Exception $e) {
            return $this->statusHttp(400, $e);
        }

    }



}
