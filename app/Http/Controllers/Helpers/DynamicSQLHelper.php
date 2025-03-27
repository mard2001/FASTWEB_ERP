<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use App\Models\dbcons;

use PDO;
use Exception;

class DynamicSQLHelper
{

    public static function testConnection(Request $request)
    {
        set_time_limit(20);

        // Validate the connection details from the request
        $connectionDetails = $request->validate([
            'host' => 'required|string',
            'port' => 'required|numeric',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
            'driver' => 'required|string|in:mysql,pgsql,sqlite,sqlsrv', // specify accepted drivers
        ]);

        // Construct DSN based on driver
        if ($connectionDetails['driver'] === 'sqlsrv') {
            $dsn = "sqlsrv:Server={$connectionDetails['host']},{$connectionDetails['port']};Database={$connectionDetails['database']}";
        } else {
            $dsn = "{$connectionDetails['driver']}:host={$connectionDetails['host']};port={$connectionDetails['port']};dbname={$connectionDetails['database']}";
        }
        try {
            $pdo = new PDO($dsn, $connectionDetails['username'], $connectionDetails['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->query('SELECT 1'); // Test the connection

            return response()->json(['message' => 'Database connection is successful!'], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Database connection failed!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public static function dynamicDB(Request $request)
    {
        // Validate the incoming request with nested structure
        $connectionDetails = $request->validate([
            'driver' => 'required|string|in:mysql,pgsql,sqlite,sqlsrv',
            'host' => 'required|string',
            'port' => 'required|numeric', // Ensure this is included if needed
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string', // This not allowed password to be empty
            'machineIdKey' => 'string',
        ]);

        $conn = dbcons::where('machineIdKey', $request['machineIdKey'])->where('EncryptedPassword', $request['password'])->first();
        $connectionName = 'dynamic_connection';

        if ($conn) {
            Config::set("database.connections.{$connectionName}", [
                'driver' => $connectionDetails['driver'],
                'host' => $connectionDetails['host'],
                'port' => $connectionDetails['port'],
                'database' => $connectionDetails['database'],
                'username' => $connectionDetails['username'],
                'password' => $connectionDetails['password'],
                'charset' => 'utf8', // Ensure this matches your database
                'collation' => 'utf8_unicode_ci', // Ensure this matches your database
                'prefix' => '',
            ]);
            return $connectionName;
        } else {
            return null;
        }
    }


    public function convertFormData(Request $request)
    {
        // Initialize an empty array for the data
        $data = [];
        $excludedFields = ['image_file', '_method', 'conn', 'dynamicConnection'];

        // Get all input fields from the request
        $inputFields = $request->all();

        // Loop through the input fields
        foreach ($inputFields as $key => $value) {

            // Check if the field is not in the excluded fields
            if (!in_array($key, $excludedFields)) {
                $data[$key] = $value; // Store the value in the $data array
            }
        }

        return $data; // Return the associative array
    }
}
