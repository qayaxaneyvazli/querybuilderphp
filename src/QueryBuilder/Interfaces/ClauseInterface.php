<?php

namespace QueryBuilder\Interfaces;

interface ClauseInterface
{
    public function getSQL(): string;
    public function getParams(): array;
}