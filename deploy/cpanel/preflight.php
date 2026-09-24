<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$buildCheck = in_array('--build-check', $argv, true);
$failures = [];
if (PHP_VERSION_ID < 80400) {
    $failures[] = 'PHP 8.4 or later is required by composer.lock.';
}
foreach (['ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'mbstring', 'openssl', 'PDO', 'pdo_mysql', 'session', 'SimpleXML', 'tokenizer', 'xml'] as $extension) {
    if (! extension_loaded($extension)) {
        $failures[] = 'Missing PHP extension: '.$extension;
    }
}
if (! is_callable('proc_open')) {
    $failures[] = 'proc_open is needed by the Laravel scheduler.';
}
$publicPath = require __DIR__.'/public-path.php';
foreach (['vendor/autoload.php', 'bootstrap/app.php'] as $path) {
    if (! is_file(__DIR__.'/'.$path)) {
        $failures[] = 'Missing application file: '.$path;
    }
}
foreach (['build/manifest.json', 'admin-build/manifest.json', 'index.php', '.htaccess', 'favicon.ico'] as $path) {
    if (! is_file($publicPath.'/'.$path)) {
        $failures[] = 'Missing public file: '.$path.'; check public-path.php.';
    }
}
foreach (['storage/logs', 'storage/framework/cache', 'storage/framework/views', 'storage/framework/sessions', 'bootstrap/cache'] as $path) {
    if (! is_dir(__DIR__.'/'.$path) || ! is_writable(__DIR__.'/'.$path)) {
        $failures[] = 'Directory must be writable: '.$path;
    }
}
if (! $buildCheck && ! is_file(__DIR__.'/.env')) {
    $failures[] = 'Create .env from .env.example and enter the hosting settings.';
}
if ($failures) {
    fwrite(STDERR, implode("\n", $failures)."\n");
    exit(1);
}
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! $buildCheck) {
    if (! $app->environment('production') || config('app.debug') || ! config('app.key')) {
        $failures[] = 'Require APP_ENV=production, APP_DEBUG=false and a generated APP_KEY.';
    }
    if (config('database.default') !== 'mysql') {
        $failures[] = 'Configure the production MySQL database.';
    }
    if (! str_starts_with((string) config('app.url'), 'https://') || ! config('session.secure')) {
        $failures[] = 'Configure HTTPS APP_URL and SESSION_SECURE_COOKIE=true.';
    }
    if (in_array('--database', $argv, true)) {
        try {
            DB::connection()->getPdo();
            if (! Schema::hasTable('integration_connections')) {
                $failures[] = 'Database connected; migrations are not complete.';
            }
        } catch (Throwable) {
            $failures[] = 'Database connection failed; check the private .env settings.';
        }
    }
    if (config('mail.default') === 'log') {
        echo "NOTICE: email delivery is not enabled; configure SMTP before launch.\n";
    }
}
if (! extension_loaded('pcntl')) {
    echo "NOTICE: pcntl is unavailable; CLI job timeouts depend on hosting process limits.\n";
}
if ($failures) {
    fwrite(STDERR, implode("\n", $failures)."\n");
    exit(1);
}
echo 'Preflight OK. PHP '.PHP_VERSION."; public assets and application paths verified.\n";
