<?php

namespace Database\Seeders;

use App\Services\Theme\ThemeService;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run(ThemeService $themes): void
    {
        $themes->sync();

        if ($themes->active() === null) {
            $themes->activate('solve-default');
        }
    }
}
