<?php

namespace QueryBuilder\Clauses;

use QueryBuilder\Interfaces\ClauseInterface;

class WhereClause implements ClauseInterface
{
    private array $conditions = [];

    public function where(string $column, string $operator, $value): self
    {
        $this->conditions[] = ['AND', $column, $operator, $value];
        return $this;
    }

    public function orWhere(string $column, string $operator, $value): self
    {
        $this->conditions[] = ['OR', $column, $operator, $value];
        return $this;
    }

    public function getSQL(): string
    {
        if (empty($this->conditions)) {
            return '';
        }

        $sql = 'WHERE ';
        foreach ($this->conditions as $index => $condition) {
            [$conjunction, $column, $operator, $value] = $condition;
            if ($index === 0) {
                $conjunction = '';
            }
            $sql .= "$conjunction $column $operator ?";
        }
        return $sql;
    }

     public function getParams(): array
    {
        return array_column($this->conditions, 3);
    }
}