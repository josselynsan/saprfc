<?php

namespace App\Services;
use Illuminate\Support\Facades\Log;

use SAPNWRFC\Connection as SapConnection;
use SAPNWRFC\Exception as SapException;
use SAPNWRFC\ConnectionException as SapConnectionException;


class SapConnectionService
{
    protected $connection;

    public function __construct()
    {
        $parameters = [
            'ashost' => env('SAP_ASHOST'),
            'sysnr'  => env('SAP_SYSNR'),
            'client' => env('SAP_CLIENT'),
            'user' => env('SAP_USER'),
            'passwd' => env('SAP_PASSWD'),
            /** si se neceita conectar a traves del saprouter, descomente la siguiente linea */ 
            //'saprouter' => env('SAP_SAPROUTER'),
        ];

        SapConnection::setGlobalLogonTimeout(10);

        try {
            $this->connection = new SapConnection($parameters);

        } catch (SapConnectionException $e) {
            $error = "{$e->getMessage()} \n {$e->getTraceAsString()}";

            log::info("PART 1");
            log::info($e->getErrorInfo());
            log::info($error);
            return ['error' => $e->getErrorInfo()];

        } catch (SapException $e) {
            $error = "{$e->getMessage()} \n {$e->getTraceAsString()}";

            log::info("PART 2");
            log::info($e->getErrorInfo());
            return ['error' => $e->getErrorInfo()];
        } catch (\Exception $e) {
            log::info("PART 3");
            log::info($e->getErrorInfo());
            return ['error' => $e->getErrorInfo()];
        }

    }

    public function callRFC($functionName, $parameters)
    {
        try {
            $rfcFunction = $this->connection->getFunction($functionName);
            if (!$rfcFunction) {
                throw new \Exception("Function $functionName not found in SAP.");
            }

            $result = $rfcFunction->invoke($parameters);
            return $result;
        } catch (SapException $e) {
            return ['error' => $e->getMessage()];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function close()
    {

            try {
                $this->connection->close();
    
            } catch (SapConnectionException $e) {
                $error = "{$e->getMessage()} \n {$e->getTraceAsString()}";
    
                log::info("PART 4");
                log::info($e->getErrorInfo());
                return ['error' => $e->getErrorInfo()];
    
            } catch (SapException $e) {
                $error = "{$e->getMessage()} \n {$e->getTraceAsString()}";
    
                log::info("PART 5");
                log::info($e->getErrorInfo());
                return ['error' => $e->getErrorInfo()];
            } catch (\Exception $e) {
                log::info("PART 6");
                log::info($e->getErrorInfo());
                return ['error' => $e->getErrorInfo()];
            }
    

    }
}
