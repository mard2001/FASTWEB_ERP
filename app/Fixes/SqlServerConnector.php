<?php

namespace App\Fixes;

use Illuminate\Database\Connectors\SqlServerConnector as BaseConnector;
use Illuminate\Database\Connectors\ConnectorInterface;

use PDO;
use PDOException;

class SqlServerConnector extends BaseConnector implements ConnectorInterface
{

    /**
     * Create a new PDO connection instance.
     *
     * @param  string  $dsn
     * @param  string  $username
     * @param  string  $password
     * @param  array  $options
     * @return \PDO
     */
    protected function createPdoConnection($dsn, $username, $password, $options)
    {

        $pdo = new PDO($dsn, $username, $password);

        foreach ($options as $attribute => $value) {
            try {
                $pdo->setAttribute($attribute, $value);
            } catch (PDOException $e) {
                //
            }
        }

        return $pdo;

    }

}