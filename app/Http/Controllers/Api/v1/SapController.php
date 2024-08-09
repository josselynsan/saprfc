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

        $this->sapService->close();

        return response()->json(['message' => 'Operación completada con éxito']);
    }



    public function saprfc(Request $request)
    {
        try{



            if (empty($request->input('functionName')))  return $this->statusHttp(400); 
            /*
            if (empty($request->input('parameters')))  return $this->statusHttp(400); 
            */

            $functionName = $request->input('functionName');
            $parameters = $request->input('parameters');

            
            

            

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


    /** PROBADA */
    /** PRIORIDAD (5) */

    /*
        openapi: 3.0.0
        info:
        title: API Documentation
        version: 1.0.0
        paths:
        /consultas/avisos:
            post:
            summary: Consulta avisos
            description: Obtiene información de los avisos basada en los parámetros proporcionados.
            operationId: ZSGDEA_CONSULTA_AVISOS
            requestBody:
                required: true
                content:
                application/json:
                    schema:
                    type: object
                    properties:
                        iCentro:
                        type: string
                        description: Centro de información (opcional)
                        iFechaIni:
                        type: string
                        format: date
                        description: Fecha de inicio
                        iHoraIni:
                        type: string
                        format: time
                        description: Hora de inicio
                        iFechaFin:
                        type: string
                        format: date
                        description: Fecha de fin
                        iHoraFin:
                        type: string
                        format: time
                        description: Hora de fin
                        notifType:
                        type: array
                        items:
                            type: string
                        description: Tipo(s) de notificación
                        asttx:
                        type: array
                        items:
                            type: string
                        description: Estado(s) de aviso
                        vkont:
                        type: array
                        items:
                            type: string
                        description: Cuenta(s) de contrato
            responses:
                '200':
                description: Respuesta exitosa con los datos de los avisos
                content:
                    application/json:
                    schema:
                        type: object
                        properties:
                        origin:
                            type: string
                            example: serverSapRfc
                        transactionId:
                            type: string
                            example: 1234567890
                        timestamp:
                            type: string
                            format: date-time
                            example: 2024-08-01T12:00:00Z
                        status:
                            type: boolean
                            example: true
                        data:
                            type: object
                            description: Datos obtenidos de la consulta
                        message:
                            type: string
                            example: success
                '400':
                description: Error en la solicitud
                content:
                    application/json:
                    schema:
                        type: object
                        properties:
                        origin:
                            type: string
                            example: serverSapRfc
                        transactionId:
                            type: string
                            example: 1234567890
                        timestamp:
                            type: string
                            format: date-time
                            example: 2024-08-01T12:00:00Z
                        status:
                            type: boolean
                            example: false
                        message:
                            type: string
                            example: Error details here

    */
    public function ZSGDEA_CONSULTA_AVISOS(Request $request)
    {
        try{

            $estadosArray = [];
            if (is_array($request->asttx) && !empty($request->asttx)) {
                foreach ($request->asttx as $estado) {
                    if (!empty($estado)) { 
                        $estadosArray[] = ["ASTTX" => $estado];
                    }
                }
            }

            $cuentascontratoArray = [];
            if (is_array($request->vkont) && !empty($request->vkont)) {
                foreach ($request->vkont as $cuentacontrato) {
                    if (!empty($cuentacontrato)) { 
                        $cuentascontratoArray[] = ["VKONT" => $cuentacontrato];
                    }
                }
            }

            $clasesArray = [];
            if (is_array($request->notifType) && !empty($request->notifType)) {
                foreach ($request->notifType as $clase) {
                    if (!empty($clase)) { 
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



    /** PROBADA */
    /** PRIORIDAD (6) */
    /*

        openapi: 3.0.0
        info:
        title: API Documentation
        version: 1.0.0
        paths:
        /detalle/aviso:
            post:
            summary: Obtener detalle de aviso
            description: Obtiene el detalle de un aviso basado en el número proporcionado.
            operationId: ZSGDEA_DETALLE_AVISO
            requestBody:
                required: true
                content:
                application/json:
                    schema:
                    type: object
                    properties:
                        iNumero:
                        type: string
                        description: Número del aviso
                        example: "1234567890"
            responses:
                '200':
                description: Respuesta exitosa con el detalle del aviso
                content:
                    application/json:
                    schema:
                        type: object
                        properties:
                        origin:
                            type: string
                            example: serverSapRfc
                        transactionId:
                            type: string
                            example: 1234567890
                        timestamp:
                            type: string
                            format: date-time
                            example: 2024-08-01T12:00:00Z
                        status:
                            type: boolean
                            example: true
                        data:
                            type: object
                            description: Detalles del aviso obtenidos de la consulta
                        message:
                            type: string
                            example: success
                '400':
                description: Error en la solicitud
                content:
                    application/json:
                    schema:
                        type: object
                        properties:
                        origin:
                            type: string
                            example: serverSapRfc
                        transactionId:
                            type: string
                            example: 1234567890
                        timestamp:
                            type: string
                            format: date-time
                            example: 2024-08-01T12:00:00Z
                        status:
                            type: boolean
                            example: false
                        message:
                            type: string
                            example: Error details here


    */
    public function ZSGDEA_DETALLE_AVISO($iNumero)
    {
        try{

            
            $functionName = 'ZSGDEA_DETALLE_AVISO';
            $parameters = [
                'I_NUMERO' => $iNumero,
                'I_GRUPO_AVISOS' => 'SGDEA',
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





    /** PROBADA */
    /** PRIORIDAD (9) */
    /*

        openapi: 3.0.0
        info:
        title: API Documentation
        version: 1.0.0
        paths:
        /personal/habilitado:
            post:
            summary: Obtener personal habilitado
            description: Obtiene información sobre el personal habilitado basado en el centro de costo proporcionado.
            operationId: ZSGDEA_PERSONAL_HABILITADO
            requestBody:
                required: true
                content:
                application/json:
                    schema:
                    type: object
                    properties:
                        iCentroCosto:
                        type: string
                        description: Centro de costo para el cual se consulta el personal habilitado
                        example: "3431001"
            responses:
                '200':
                description: Respuesta exitosa con la información del personal habilitado
                content:
                    application/json:
                    schema:
                        type: object
                        properties:
                        origin:
                            type: string
                            example: serverSapRfc
                        transactionId:
                            type: string
                            example: 1234567890
                        timestamp:
                            type: string
                            format: date-time
                            example: 2024-08-01T12:00:00Z
                        status:
                            type: boolean
                            example: true
                        data:
                            type: object
                            description: Información del personal habilitado obtenida de la consulta
                        message:
                            type: string
                            example: success
                '400':
                description: Error en la solicitud
                content:
                    application/json:
                    schema:
                        type: object
                        properties:
                        origin:
                            type: string
                            example: serverSapRfc
                        transactionId:
                            type: string
                            example: 1234567890
                        timestamp:
                            type: string
                            format: date-time
                            example: 2024-08-01T12:00:00Z
                        status:
                            type: boolean
                            example: false
                        message:
                            type: string
                            example: Error details here


    */

    public function ZSGDEA_PERSONAL_HABILITADO($iCentroCosto)
    {
        try{


            $functionName = 'ZSGDEA_PERSONAL_HABILITADO';
            $parameters = [
                'I_CENTRO_COSTO' => $iCentroCosto, //'3431001',
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
    /** PRIORIDAD (3) */

    /*

        openapi: 3.0.0
        info:
        title: API Documentation
        version: 1.0.0
        paths:
        /detalles/cta-contrato:
            post:
            summary: Obtener detalles de cuentas de contrato
            description: Obtiene información detallada sobre las cuentas de contrato basadas en los datos proporcionados.
            operationId: ZSGDEA_DETALLES_CTA_CONTRATO
            requestBody:
                required: true
                content:
                application/json:
                    schema:
                    type: object
                    properties:
                        tableLine:
                        type: array
                        items:
                            type: string
                        description: Lista de cuentas de contrato para las cuales se obtendrán los detalles.
                        example:
                            - "0001234567"
                            - "0002345678"
            responses:
                '200':
                description: Respuesta exitosa con la información detallada de las cuentas de contrato
                content:
                    application/json:
                    schema:
                        type: object
                        properties:
                        origin:
                            type: string
                            example: serverSapRfc
                        transactionId:
                            type: string
                            example: 1234567890
                        timestamp:
                            type: string
                            format: date-time
                            example: 2024-08-01T12:00:00Z
                        status:
                            type: boolean
                            example: true
                        data:
                            type: object
                            description: Información detallada sobre las cuentas de contrato obtenida de la consulta
                        message:
                            type: string
                            example: success
                '400':
                description: Error en la solicitud
                content:
                    application/json:
                    schema:
                        type: object
                        properties:
                        origin:
                            type: string
                            example: serverSapRfc
                        transactionId:
                            type: string
                            example: 1234567890
                        timestamp:
                            type: string
                            format: date-time
                            example: 2024-08-01T12:00:00Z
                        status:
                            type: boolean
                            example: false
                        message:
                            type: string
                            example: Error details here


    */
    public function ZSGDEA_DETALLES_CTA_CONTRATO($cuentaContratoId)
    {
        try{


            $cuentascontratoArray = [];
            $arrayCuentaContrato = [$cuentaContratoId];
            if (is_array($arrayCuentaContrato) && !empty($arrayCuentaContrato)) {
                foreach ($arrayCuentaContrato as $cuentacontrato) {
                    if (!empty($cuentacontrato)) {
                        $cuentascontratoArray[] = ["CUENTA_CONTRATO" => $cuentacontrato];
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




    /** PROBADA */
    /** PRIORIDAD (10) */

    /*

        openapi: 3.0.0
        info:
        title: API Documentation
        version: 1.0.0
        paths:
        /consulta/medidas:
            post:
            summary: Consultar medidas
            description: Obtiene información sobre medidas basadas en las clases y fechas proporcionadas.
            operationId: ZSGDEA_CONSULTA_MEDIDAS
            requestBody:
                required: true
                content:
                application/json:
                    schema:
                    type: object
                    properties:
                        iFechaIni:
                        type: string
                        format: date
                        description: Fecha de inicio para la consulta de medidas
                        example: "2024-01-01"
                        iFechaFin:
                        type: string
                        format: date
                        description: Fecha de fin para la consulta de medidas
                        example: "2024-12-31"
                        massn:
                        type: array
                        items:
                            type: string
                        description: Lista de clases para la consulta de medidas
                        example:
                            - "001"
                            - "002"
            responses:
                '200':
                description: Respuesta exitosa con la información de las medidas
                content:
                    application/json:
                    schema:
                        type: object
                        properties:
                        origin:
                            type: string
                            example: serverSapRfc
                        transactionId:
                            type: string
                            example: 1234567890
                        timestamp:
                            type: string
                            format: date-time
                            example: 2024-08-01T12:00:00Z
                        status:
                            type: boolean
                            example: true
                        data:
                            type: object
                            description: Información de las medidas obtenida de la consulta
                        message:
                            type: string
                            example: success
                '400':
                description: Error en la solicitud
                content:
                    application/json:
                    schema:
                        type: object
                        properties:
                        origin:
                            type: string
                            example: serverSapRfc
                        transactionId:
                            type: string
                            example: 1234567890
                        timestamp:
                            type: string
                            format: date-time
                            example: 2024-08-01T12:00:00Z
                        status:
                            type: boolean
                            example: false
                        message:
                            type: string
                            example: Error details here


    */
    public function ZSGDEA_CONSULTA_MEDIDAS($iFechaIni, $iFechaFin)
    {
        try{


            if ($iFechaIni == NULL){
                $iFechaIni = "";
            }

            if ($iFechaFin == NULL){
                $iFechaFin = "";
            }

            $functionName = 'ZSGDEA_CONSULTA_MEDIDAS';
            $parameters = [
                'I_FECHA_INI' => "$iFechaIni",
                'I_FECHA_FIN' => $iFechaFin,
                "I_CLASES" => [
                    ["MASSN" => "MA"],
                    ["MASSN" => "MB"]
                ],

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

    /** PROBADA */
    /** PRIORIDAD (12) */

    /*

        openapi: 3.0.0
        info:
        title: API Documentation
        version: 1.0.0
        paths:
        /detalle/interlocutor:
            post:
            summary: Obtener detalle de interlocutor
            description: Obtiene detalles de un interlocutor basado en el contrato o el identificador del interlocutor proporcionado.
            operationId: ZPM_DETALLE_INTERLOCUTOR
            requestBody:
                required: true
                content:
                application/json:
                    schema:
                    type: object
                    properties:
                        ctaContrato:
                        type: string
                        description: Número del contrato para obtener detalles del interlocutor
                        example: "11557160"
                        interlocutor:
                        type: string
                        description: Identificador del interlocutor para obtener detalles
                        example: "10215971"
                    oneOf:
                        - required: [ctaContrato]
                        - required: [interlocutor]
            responses:
                '200':
                description: Respuesta exitosa con el detalle del interlocutor
                content:
                    application/json:
                    schema:
                        type: object
                        properties:
                        origin:
                            type: string
                            example: serverSapRfc
                        transactionId:
                            type: string
                            example: 1234567890
                        timestamp:
                            type: string
                            format: date-time
                            example: 2024-08-01T12:00:00Z
                        status:
                            type: boolean
                            example: true
                        data:
                            type: object
                            description: Detalles del interlocutor obtenidos de la consulta
                        message:
                            type: string
                            example: success
                '400':
                description: Error en la solicitud
                content:
                    application/json:
                    schema:
                        type: object
                        properties:
                        origin:
                            type: string
                            example: serverSapRfc
                        transactionId:
                            type: string
                            example: 1234567890
                        timestamp:
                            type: string
                            format: date-time
                            example: 2024-08-01T12:00:00Z
                        status:
                            type: boolean
                            example: false
                        message:
                            type: string
                            example: Error details here


    */

    /** Nota:  agregar formato con ceros a la izquierda hasta 12 caracteres $request->ctaContrato
     *         agregar formato con ceros a la izquierda hasta 10 caracteres $request->interlocutor
    */
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
    /** PRIORIDAD (2) */

    /*

        openapi: 3.0.0
        info:
        title: API Documentation
        version: 1.0.0
        paths:
        /wm/find/zona-grupo-planifica:
            post:
            summary: Buscar zona de agrupación de estructura regional
            description: Realiza una búsqueda para encontrar la zona de agrupación de una estructura regional basada en los parámetros proporcionados.
            operationId: Z_WM_FIND_ZONA_GRUPO_PLANIFICA
            requestBody:
                required: true
                content:
                application/json:
                    schema:
                    type: object
                    properties:
                        zCalle:
                        type: string
                        description: Calle para la búsqueda
                        example: "Av. Principal"
                        zHouse1:
                        type: string
                        description: Número de la casa o edificio para la búsqueda
                        example: "123"
                        zQmart:
                        type: string
                        description: Código del barrio o zona
                        example: "BQ001"
                        zCiudad:
                        type: string
                        description: Ciudad para la búsqueda
                        example: "Madrid"
                    required:
                        - zCalle
                        - zHouse1
                        - zQmart
                        - zCiudad
            responses:
                '200':
                description: Respuesta exitosa con los datos de la zona de agrupación
                content:
                    application/json:
                    schema:
                        type: object
                        properties:
                        origin:
                            type: string
                            example: server
                        transactionId:
                            type: string
                            example: 1234567890
                        timestamp:
                            type: string
                            format: date-time
                            example: 2024-08-01T12:00:00Z
                        status:
                            type: boolean
                            example: true
                        data:
                            type: object
                            description: Datos obtenidos de la búsqueda de la zona de agrupación
                        message:
                            type: string
                            example: success
                '400':
                description: Error en la solicitud
                content:
                    application/json:
                    schema:
                        type: object
                        properties:
                        origin:
                            type: string
                            example: server
                        transactionId:
                            type: string
                            example: 1234567890
                        timestamp:
                            type: string
                            format: date-time
                            example: 2024-08-01T12:00:00Z
                        status:
                            type: boolean
                            example: false
                        message:
                            type: string
                            example: Error details here


    */
    public function Z_WM_FIND_ZONA_GRUPO_PLANIFICA(Request $request)
    {
        try{

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


    public function ZSGDEA_CONSULTA_SOLICITUDES(Request $request)
    {
        try{
            /*
            $radicadosArray = [];
            if (is_array($request->radicado) && !empty($request->radicado)) {
                foreach ($request->radicado as $rad) {
                    if (!empty($rad)) { 
                        $radicadosArray[] = ["RADICADO" => $rad];
                    }
                }
            }
            */

            $parametrosArray = [];
            if (is_array($request->parametros) && !empty($request->radicado)) {
                foreach ($request->parametros as $value) {
                    if (!empty($value)) { 
                        $parametrosArray[] = [
                            "RADICADO" => $value->radicados,
                            "CONTACTO" => $value->contacto,
                            "CUENTA_CONTRATO" => $value->cuentaContrato,
                            "INTERLOCUTOR" => $value->interlocutor,

                        ];
                    }
                }
            }


            $iEstado = '1';
            if (!empty($request->iEstado)) {
                $iEstado = $request->iEstado;
            }
            //Consulta de Información básica de los avisos
            $functionName = 'ZSGDEA_CONSULTA_SOLICITUDES';
            $parameters = [
                'I_CENTRO' => $request->iCentro, // (opcional) -> si viene vacio, no debe incluirse
                'I_ESTADO' => $iEstado, 
                'I_FECHA_INI' => $request->iFechaIni,
                'I_HORA_INI' => $request->iHoraIni,
                'I_FECHA_FIN' => $request->iFechaFin,
                'I_HORA_FIN' => $request->iHoraFin,
                "I_PARAMETROS" => $parametrosArray,
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
    
}
