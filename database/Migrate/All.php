<?php

namespace Database\Migrate;

use App\Core\Migrations;
use Throwable;

class All extends Migrations
{
	public function up(): void
	{
		$this->runAll('up');
	}

	public function down(): void
	{
		$this->runAll('down');
	}

	private function runAll(string $action): void
	{
		$migrationDir = __DIR__;
		$files = glob($migrationDir . DIRECTORY_SEPARATOR . '*.php') ?: [];
		$classes = [];

		sort($files);

		foreach ($files as $file) {
			$className = pathinfo($file, PATHINFO_FILENAME);

			if ($className === 'All') {
				continue;
			}

			require_once $file;
			$fullClass = __NAMESPACE__ . "\\{$className}";

			if (!class_exists($fullClass)) {
				echo "⚠️ Class {$fullClass} tidak ditemukan, dilewati\n";
				continue;
			}

			$instance = new $fullClass();

			if (!method_exists($instance, $action)) {
				echo "⚠️ Method {$action}() tidak ada di {$fullClass}, dilewati\n";
				continue;
			}

			$classes[] = $instance;
		}

		if ($action === 'down') {
			$classes = array_reverse($classes);
		}

		foreach ($classes as $migration) {
			$migrationClass = get_class($migration);

			try {
				echo "🚀 Running migration: {$migrationClass} ({$action})\n";
				$migration->{$action}();
			} catch (Throwable $e) {
				echo "❌ Gagal menjalankan {$migrationClass} ({$action}): {$e->getMessage()}\n";
				throw $e;
			}
		}

		echo "✅ Semua migration selesai ({$action})\n";
	}
}