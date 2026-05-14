<?php

namespace App\Cms\Repositories;

use App\Core\DB;

class SeoSettingRepository
{
    public function getByKey(string $key, string $default = ''): string
    {
        $row = DB::first('SELECT `value` FROM settings WHERE `key` = :key LIMIT 1', ['key' => $key]);

        return $row['value'] ?? $default;
    }

    public function set(string $key, string $value, string $group = 'seo', string $type = 'string'): void
    {
        $exists = DB::first('SELECT id FROM settings WHERE `key` = :key LIMIT 1', ['key' => $key]);

        if ($exists) {
            DB::statement(
                'UPDATE settings SET `value` = :value, `group` = :group, `type` = :type, updated_at = NOW() WHERE `key` = :key',
                [
                    'key' => $key,
                    'value' => $value,
                    'group' => $group,
                    'type' => $type,
                ]
            );

            return;
        }

        DB::statement(
            'INSERT INTO settings (`key`, `value`, `group`, `type`) VALUES (:key, :value, :group, :type)',
            [
                'key' => $key,
                'value' => $value,
                'group' => $group,
                'type' => $type,
            ]
        );
    }
}
