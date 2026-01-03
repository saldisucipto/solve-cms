<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'users';

    public int $id;
    public string $name;
    public string $email;
    public string $password;
    public string $role;
}
