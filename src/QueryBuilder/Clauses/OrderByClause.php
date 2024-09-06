<?php

namespace QueryBuilder\Clauses;

use QueryBuilder\Interfaces\ClauseInterface;

class OrderByClause implements ClauseInterface
{
    private array $orders = [];

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orders[] = [$column, $direction];
        return $this;
    }

    public function getSQL(): string
    {
        if (empty($this->orders)) {
            return '';
        }

        return 'ORDER BY ' . implode(', ', array_map(fn($order) => "{$order[0]} {$order[1]}", $this->orders));
    }

    public function getParams(): array
    {
        return [];
    }
}