<?php

namespace App\Core;

use App\Core\Router;

class App
{
    protected Router $router; // dependancy injector
    public function __construct()
    {
        $this->router = new Router();
    }

    /**
     * Fungsi RUN ini adalah fungsi utama yang akan di triger oleh aplikasi
     *
     * @return void
     */
    public function run(): void
    {
        // Load Routes
        $this->loadRoutes();

        // Dispatch Request
        $this->router->dispatch();
    }

    /**
     * Fungsi ini berguna untuk membaca routes secara dinamis
     *
     * @return void
     */
    protected function loadRoutes(): void
    {
        $router = $this->router;

        require BASE_PATH . '/routes/web.php';
        require BASE_PATH . '/routes/admin.php';
    }
}
