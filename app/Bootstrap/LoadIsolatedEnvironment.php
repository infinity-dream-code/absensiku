<?php

namespace App\Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\Adapter\ServerConstAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Env;

/**
 * Apache + PHP thread-safe di Windows berbagi getenv()/putenv() antar semua
 * aplikasi dalam satu proses, sehingga .env aplikasi lain (DB, APP_KEY,
 * APP_URL) bisa terbaca di sini. Loader ini tidak memakai putenv dan
 * menimpa nilai yang sudah ada dengan isi .env absensi.
 */
class LoadIsolatedEnvironment extends LoadEnvironmentVariables
{
    public function bootstrap(Application $app)
    {
        Env::disablePutenv();

        parent::bootstrap($app);
    }

    protected function createDotenv($app)
    {
        $repository = RepositoryBuilder::createWithNoAdapters()
            ->addAdapter(ServerConstAdapter::class)
            ->addAdapter(EnvConstAdapter::class)
            ->make();

        return Dotenv::create(
            $repository,
            $app->environmentPath(),
            $app->environmentFile()
        );
    }
}
