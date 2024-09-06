<?php

namespace QueryBuilder;

use QueryBuilder\Interfaces\QueryBuilderInterface;
use QueryBuilder\Interfaces\ConnectionInterface;
use QueryBuilder\Exceptions\QueryBuilderException;
use QueryBuilder\Clauses\WhereClause;
use QueryBuilder\Clauses\OrderByClause;
use QueryBuilder\Connections\PDOConnection;


class QueryBuilder implements QueryBuilderInterface
{
    private array $columns = ['*'];
    private string $table = '';
    private array $wheres = [];
    private int $limit = 0;
    private int $offset = 0;
    private array $orderBy = [];

    private string $queryType = 'SELECT'; 

    public function __construct(private ConnectionInterface $connection)
    {
        $this->reset();  

    }

    public function select(array $columns = ['*']): self
    {
        $this->columns = $columns;
        $this->queryType = 'SELECT';
        return $this;
    }

    public function from(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function where(string $column, string $operator, $value): self
    {
        $this->whereClause->where($column, $operator, $value);
        return $this;
    }

    public function orWhere(string $column, string $operator, $value): self
    {
        $this->whereClause->orWhere($column, $operator, $value);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderByClause->orderBy($column, $direction);
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }



  

    public function insertInto(string $table): self
    {
        $this->table = $table;
        $this->queryType = 'INSERT';
        return $this;
    }
    public function values(array $values): self
    {
        $this->values = $values;
        return $this;
    }

    public function update(string $table): self
    {
        $this->table = $table;
        $this->queryType = 'UPDATE';
        return $this;
    }
    public function set(array $sets): self
    {
        $this->sets = $sets;
        return $this;
    }
    public function deleteFrom(string $table): self
    {
        $this->table = $table;
        $this->queryType = 'DELETE';
        return $this;
    }

    public function getQuery(): string
    {
        if (empty($this->table)) {
            throw new QueryBuilderException("Table name is required");
        }

        switch ($this->queryType) {
            case 'INSERT':
                return $this->buildInsertQuery();
            case 'UPDATE':
                return $this->buildUpdateQuery();
            case 'DELETE':
                return $this->buildDeleteQuery();
            case 'SELECT':
            default:
                return $this->buildSelectQuery();
        }
    }

    private function buildSelectQuery(): string
    {
        $query = "SELECT " . implode(', ', $this->columns) . " FROM " . $this->table;

        $whereSQL = $this->whereClause->getSQL();
        if (!empty($whereSQL)) {
            $query .= ' ' . $whereSQL;
        }

        $orderBySQL = $this->orderByClause->getSQL();
        if (!empty($orderBySQL)) {
            $query .= ' ' . $orderBySQL;
        }

        if ($this->limit > 0) {
            $query .= " LIMIT " . $this->limit;
        }

        if ($this->offset > 0) {
            $query .= " OFFSET " . $this->offset;
        }

        return $query;
    }

    private function buildInsertQuery(): string
    {
        $columns = implode(', ', array_keys($this->values));
        $placeholders = implode(', ', array_fill(0, count($this->values), '?'));
        return "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
    }

    private function buildUpdateQuery(): string
    {
        $setClause = implode(', ', array_map(fn($col) => "$col = ?", array_keys($this->sets)));
        $query = "UPDATE {$this->table} SET {$setClause}";

        $whereSQL = $this->whereClause->getSQL();
        if (!empty($whereSQL)) {
            $query .= ' ' . $whereSQL;
        }

        return $query;
    }
    private function buildDeleteQuery(): string
    {
     
        $query = "DELETE FROM {$this->table}";

        $whereSQL = $this->whereClause->getSQL();
        if (!empty($whereSQL)) {
            $query .= ' ' . $whereSQL;
        }

        return $query;
    }

    private function buildWhereClause(): string
    {
        $whereClauses = [];
        foreach ($this->wheres as $index => $where) {
            [$conjunction, $column, $operator, $value] = $where;
            if ($index === 0) {
                $conjunction = '';
            }
            $whereClauses[] = "$conjunction $column $operator ?";
        }
        return implode(' ', $whereClauses);
    }

    private function buildOrderByClause(): string
    {
        return implode(', ', array_map(fn($order) => "{$order[0]} {$order[1]}", $this->orderBy));
    }

    public function execute(): mixed
    {
        $query = $this->getQuery();
        $params = $this->getParams();

        // Hata ayıklama için loglar
        error_log("Executing {$this->queryType} query: " . $query);
        error_log("Parameters: " . print_r($params, true));

        try {
            $result = $this->connection->execute($query, $params);
            
            // Etkilenen satır sayısını loglayalım
            if ($result instanceof \PDOStatement) {
                error_log("Affected rows: " . $result->rowCount());
            }
        } catch (\PDOException $e) {
            
            throw $e;
        }

        $this->reset();
        return $result;
    }


    private function getParams(): array
    {
        $params = [];

        if (!empty($this->values)) {
            $params = array_values($this->values);
        } elseif (!empty($this->sets)) {
            $params = array_values($this->sets);
        }

        return array_merge($params, $this->whereClause->getParams());
    }

    private function getWhereParams(): array
    {
        return array_column($this->wheres, 3);
    }

    private function reset(): void
    {
        $this->columns = ['*'];
        $this->table = '';
        $this->whereClause = new WhereClause();
        $this->orderByClause = new OrderByClause();
        $this->limit = 0;
        $this->offset = 0;
        $this->values = [];
        $this->sets = [];
        $this->queryType = 'SELECT'; 
    }
}