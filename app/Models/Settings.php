<?php

namespace App\Models;

use App\Core\DB;

class Settings extends Model
{
    protected string $table = 'settings';


    public function updateOrCreate(string $key, $value, $group = null, $type = null, $id = null)
    {
        $isExist = $this->find($id);

        if ($isExist) {
            $statement =  DB::conn()->prepare("UPDATE {$this->table} SET {$key} = {$value} WHERE id = :id");
            $statement->execute(['id' => $id]);
        } else {
            $statement = DB::conn()->prepare("INSERT INTO {$this->table} (key, value, group, type) VALUES (:key, :value, :group, :type)");
            $statement->execute([
                'key' => $key,
                'value' => $value,
                'group' => $group,
                'type' => $type
            ]);
        }
    }
}
