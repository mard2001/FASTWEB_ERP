<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;

trait dbconfigs
{
    public function getFastSFADBConfig()
    {
        //database config
        $conn = 'FastSFADB';
        Config::set("database.connections.{$conn}", [
            'driver' => 'sqlsrv',
            'host' => '66.42.43.247',
            'port' => '8055',
            'database' => 'FastSFA',
            'username' => 'fastsfa',
            'password' => 'd#M6ZIWW.22(', // Use single quotes to avoid escaping double quotes
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);

        return $conn;
    }
}
