<?php

namespace App\Core;

use App\Core\Router;
use App\Services\SettingService;
use Dotenv\Dotenv;

class App
{
    protected Router $router; // dependancy injector
    protected CmsKernel $cms;

    public function __construct()
    {
        $this->loadEnv();
        $this->bootCms();
        $this->router = new Router();
    }

    protected function loadEnv()
    {
        if (file_exists(BASE_PATH . '/.env')) {
            $dotEnv = Dotenv::createImmutable(BASE_PATH);
            $dotEnv->load();
        }
    }

    /**
     * Fungsi RUN ini adalah fungsi utama yang akan di triger oleh aplikasi
     *
     * @return void
     */
    public function run(): void
    {
        // Hook lifecycle awal request
        Cms::hooks()->doAction('app.booting');

        // Load Routes
        $this->loadRoutes();

        // Load General Settings
        $this->loadGeneralSetting();

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

    protected function loadGeneralSetting()
    {
        SettingService::load();
    }

    protected function bootCms(): void
    {
        $this->cms = new CmsKernel(BASE_PATH . '/modules');
        $this->cms->boot();
        Cms::setKernel($this->cms);
    }
}
