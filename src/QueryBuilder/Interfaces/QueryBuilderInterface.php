<?php
namespace QueryBuilder\Interfaces;

interface QueryBuilderInterface
{
    public function select(array $columns = ['*']): self;
    public function from(string $table): self;
    public function where(string $column, string $operator, $value): self;
    public function orWhere(string $column, string $operator, $value): self;
    public function limit(int $limit): self;
    public function offset(int $offset): self;
    public function orderBy(string $column, string $direction = 'ASC'): self;
    public function insertInto(string $table): self;
    public function values(array $values): self;
    public function update(string $table): self;
    public function set(array $sets): self;
    public function deleteFrom(string $table): self;
    public function execute(): mixed;
    public function getQuery(): string;
}