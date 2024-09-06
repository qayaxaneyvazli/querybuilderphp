<?php
namespace QueryBuilder\Interfaces;

interface ConnectionInterface
{
    public function connect(): void;
    public function disconnect(): void;
    public function execute(string $query, array $params = []): mixed;
    public function lastInsertId(): string;
    public function beginTransaction(): bool;
    public function commit(): bool;
    public function rollBack(): bool;
}