<?php

use QueryBuilder\Connections\PDOConnection;

function createConnection(): PDOConnection
{
    $config = require __DIR__ . '/../../config/database.php';
    
    $dsn = sprintf(
        '%s:host=%s;dbname=%s;charset=%s',
        $config['driver'],
        $config['host'],
        $config['database'],
        $config['charset']
    );

    return new PDOConnection(
        $dsn,
        $config['username'],
        $config['password'],
        $config['options'] ?? []
    );
}