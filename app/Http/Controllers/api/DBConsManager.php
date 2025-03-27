<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Models\dbcons;


class DBConsManager
{

    public function saveDbconPassword(Request $request)
    {
        $responseMessage = array();
        try {

            $conn = dbcons::where('machineIdKey', $request['machineIdKey'])->first();
            $hashedPass = Hash::make($request['password']);

            if ($conn) {
                $conn->EncryptedPassword = $hashedPass;
                $conn->PlainTextPassword = $request['password'];
                $conn->save();
            } else{
                $newConn = new dbcons();
                $newConn->machineIdKey = $request['machineIdKey'];
                $newConn->EncryptedPassword = $hashedPass;
                $newConn->PlainTextPassword = $request['password'];
                $newConn->save();
            }

            $responseMessage = [
                'response' => 'Account Registered',
                'Hashed' => $hashedPass,
                'status_response' => 1,
            ];
        } catch (\Exception $e) {
            $responseMessage = [
                'response' => $e->getMessage(),
                'status_response' => 0,
            ];
        }

        return response()->json($responseMessage);
    }
}
