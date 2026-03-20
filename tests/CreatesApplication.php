<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $this->forgetCachedBootstrapFiles();

        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Remove framework caches that would ignore PHPUnit environment variables.
     *
     * @see https://laravel.com/docs/configuration#configuration-caching
     */
    protected function forgetCachedBootstrapFiles(): void
    {
        $bootstrapCache = realpath(__DIR__ . '/../bootstrap/cache');

        if ($bootstrapCache === false) {
            return;
        }

        $paths = [
            $bootstrapCache . '/config.php',
        ];

        foreach (glob($bootstrapCache . '/routes-*.php') ?: [] as $routesCache) {
            $paths[] = $routesCache;
        }

        foreach ($paths as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
