<?php

namespace App\Services;


use App\Core\DB;

class AnalyticsService
{
    public static function track(string $slug): void
    {
        // assign tanggal
        $date = date('Y-m-d');

        // Halaman 
        DB::statement("INSERT INTO analytics_pages (slug, visit_date, total_visits) VALUES (:slug, :visit_date, 1)
            ON DUPLICATE KEY UPDATE total_visits = total_visits + 1", [
            'slug' => $slug,
            'visit_date' => $date
        ]);
    }
}
