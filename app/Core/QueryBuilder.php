<?php

namespace App\Core;

use App\Core\Model;

class QueryBuilder
{
    protected string $table;
    protected string $model;
    protected string $column;
    protected mixed $value;


    public function __construct($table, $model, $column, $value)
    {
        $this->table  = $table;
        $this->model  = $model;
        $this->column = $column;
        $this->value  = $value;
    }


    public function first()
    {
        $sql = sprintf(
            "SELECT * FROM %s WHERE %s = :val LIMIT 1",
            $this->table,
            $this->column
        );

        $stmt = DB::conn()->prepare($sql);
        $stmt->execute(['val' => $this->value]);

        return $stmt->fetchObject($this->model);
    }
}
