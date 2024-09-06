<?php

namespace QueryBuilder\Connections;

use QueryBuilder\Interfaces\ConnectionInterface;
use PDO;
use PDOException;

class PDOConnection implements ConnectionInterface
{
    private ?PDO $connection = null;
    private array $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    public function __construct(
        private string $dsn,
        private ?string $username = null,
        private ?string $password = null,
        private array $driverOptions = []
    ) {
        $this->options = array_merge($this->options, $driverOptions);
    }

    public function connect(): void
    {
        if ($this->connection === null) {
            try {
                $this->connection = new PDO($this->dsn, $this->username, $this->password, $this->options);
            } catch (PDOException $e) {
                throw new PDOException("Connection failed: " . $e->getMessage());
            }
        }
    }

    public function disconnect(): void
    {
        $this->connection = null;
    }

    public function execute(string $query, array $params = []): mixed
    {
        $this->connect();
        $statement = $this->connection->prepare($query);
        $statement->execute($params);
        return $statement;
    }

    public function getConnection(): PDO
    {
        $this->connect();
        return $this->connection;
    }

    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollBack(): bool
    {
        return $this->connection->rollBack();
    }
}