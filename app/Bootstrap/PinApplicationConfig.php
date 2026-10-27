<?php

namespace App\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\DB;

/**
 * Baca file .env milik aplikasi ini secara langsung, lalu timpa config.
 * Jangan percaya $_ENV / getenv() — di Apache Windows nilainya bisa
 * tertukar dengan aplikasi lain dalam proses yang sama.
 */
class PinApplicationConfig
{
    public function bootstrap(Application $app): void
    {
        $values = $this->parseDotEnv($app->environmentPath() . DIRECTORY_SEPARATOR . $app->environmentFile());
        if ($values === []) {
            return;
        }

        $config = $app['config'];
        $connection = $config->get('database.default', 'mysql');

        if (!empty($values['DB_HOST'])) {
            $config->set("database.connections.{$connection}.host", $values['DB_HOST']);
        }
        if (!empty($values['DB_PORT'])) {
            $config->set("database.connections.{$connection}.port", $values['DB_PORT']);
        }
        if (!empty($values['DB_DATABASE'])) {
            $config->set("database.connections.{$connection}.database", $values['DB_DATABASE']);
        }
        if (array_key_exists('DB_USERNAME', $values)) {
            $config->set("database.connections.{$connection}.username", $values['DB_USERNAME']);
        }
        if (array_key_exists('DB_PASSWORD', $values)) {
            $config->set("database.connections.{$connection}.password", $values['DB_PASSWORD']);
        }
        if (!empty($values['APP_KEY'])) {
            $config->set('app.key', $values['APP_KEY']);
        }
        if (!empty($values['APP_URL'])) {
            $config->set('app.url', $values['APP_URL']);
        }
        if (!empty($values['SESSION_COOKIE'])) {
            $config->set('session.cookie', $values['SESSION_COOKIE']);
        }
        if (!empty($values['SESSION_LIFETIME'])) {
            $config->set('session.lifetime', $values['SESSION_LIFETIME']);
        }

        try {
            DB::purge($connection);
        } catch (\Throwable $e) {
            // koneksi belum dibuat
        }
    }

    /**
     * @return array<string, string>
     */
    private function parseDotEnv(string $path): array
    {
        if (!is_file($path) || !is_readable($path)) {
            return [];
        }

        $out = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return [];
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_starts_with($line, 'export ')) {
                $line = trim(substr($line, 7));
            }
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if ($key === '') {
                continue;
            }

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            $out[$key] = $value;
        }

        return $out;
    }
}
