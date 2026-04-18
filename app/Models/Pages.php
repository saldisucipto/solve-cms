<?php

namespace App\Models;

class Pages extends Model
{
    protected static string $table = 'pages';

    public int $id;
    public string $title;
    public string $slug;
    public string $body;
    public string $created_at;
}
