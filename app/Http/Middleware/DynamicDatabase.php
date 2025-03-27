<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\helpers\DynamicSQLHelper; // Import the helper

class DynamicDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->validate([
            'conn' => 'required',
        ]);

        $conn = $request->input('conn');
        //return response()->json($request->all());

        $conn = is_string($conn) ? json_decode($conn, true) : $conn;

        // return response()->json($conn);

        // Use DynamicSQLHelper to get the connection name
        $dynamicConnection = DynamicSQLHelper::dynamicDB(new Request($conn));

        if (!$dynamicConnection) {
            return response()->json([
                'response' => 'Incorrect connection details',
                'status_response' => 0
            ]);
        } else {
            // Pass the connection name to the request for use in the controller
            $request->merge(['dynamicConnection' => $dynamicConnection]);
            return $next($request);
        }
    }
}
