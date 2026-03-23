<?php

namespace App\Core;

use App\Core\DB;

abstract class Migrations
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = DB::conn();
    }

    abstract public function up(): void;
    abstract public function down(): void;
}
