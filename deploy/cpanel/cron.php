<?php

use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Output\ConsoleOutput;

// CLI only. Uploaded as jar-app/cpanel-cron.php, outside the document root.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$task = $argv[1] ?? '';
$commands = [
    'schedule' => ['schedule:run', []],
    'integrations' => ['queue:work', ['connection' => 'integrations', '--queue' => 'integrations', '--stop-when-empty' => true, '--max-time' => 600, '--max-jobs' => 25, '--sleep' => 1, '--tries' => 1, '--timeout' => 900]],
    'notifications' => ['queue:work', ['connection' => 'database', '--queue' => 'default', '--stop-when-empty' => true, '--max-time' => 240, '--max-jobs' => 100, '--sleep' => 1, '--tries' => 3, '--timeout' => 60]],
];
if (! isset($commands[$task])) {
    fwrite(STDERR, "Usage: php cpanel-cron.php schedule|integrations|notifications\n");
    exit(2);
}
if (! is_file(__DIR__.'/.env')) {
    fwrite(STDERR, "Configure jar-app/.env before enabling cron.\n");
    exit(2);
}
$lock = fopen(__DIR__.'/storage/framework/cache/cpanel-'.$task.'.lock', 'c');
if (! $lock) {
    fwrite(STDERR, "Cannot create cron lock. Check storage permissions.\n");
    exit(2);
}
if (! flock($lock, LOCK_EX | LOCK_NB)) {
    exit(0);
}
try {
    chdir(__DIR__);
    require __DIR__.'/vendor/autoload.php';
    $app = require __DIR__.'/bootstrap/app.php';
    $kernel = $app->make(Kernel::class);
    [$command, $arguments] = $commands[$task];
    $status = $kernel->call($command, [...$arguments, '--no-interaction' => true, '--no-ansi' => true], new ConsoleOutput);
} catch (Throwable $error) {
    // Never echo a provider response or database exception containing credentials.
    fwrite(STDERR, 'Cron failed ('.$error::class."). Check the private application log and integration history.\n");
    $status = 1;
} finally {
    flock($lock, LOCK_UN);
    fclose($lock);
}
exit($status);
