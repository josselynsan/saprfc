<?php
namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

trait LogService {


    

    public function createdLog($modulo, $data)
    {
        Log::info([
            'idUser'      => auth()->user()->id,
            'userNameLog' => auth()->user()->username,
            'fechaLog'    => now(),
            'ipLog'       => Request::ip(),
            'moduloLog'   => $modulo,
            'eventoLog'   => $data['eventoLog'],
            'antesLog'    => $data['antesLog'],
            'despuesLog'  => $data['despuesLog']
        ]);
        return true;
    }
}