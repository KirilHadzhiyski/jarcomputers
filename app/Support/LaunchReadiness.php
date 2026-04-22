<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Schema;

class LaunchReadiness
{
    public static function report(): array
    {
        $checks = [
            self::appUrlCheck(),
            self::domainRedirectCheck(),
            self::httpsCheck(),
            self::databaseCheck(),
            self::adminAccountCheck(),
            self::mailCheck(),
            self::queueCheck(),
            self::sessionCheck(),
        ];

        return [
            'checks' => $checks,
            'summary' => [
                'ready' => collect($checks)->where('status', 'ready')->count(),
                'warning' => collect($checks)->where('status', 'warning')->count(),
                'missing' => collect($checks)->where('status', 'missing')->count(),
            ],
        ];
    }

    private static function appUrlCheck(): array
    {
        $appUrl = (string) config('app.url');
        $host = parse_url($appUrl, PHP_URL_HOST);
        $primaryDomain = (string) config('communications.domain.primary');
        $canonicalHost = (string) config('communications.domain.canonical_host');

        if (blank($host) || in_array($host, ['127.0.0.1', 'localhost'], true)) {
            return self::makeCheck(
                label: 'Домейн и APP_URL',
                status: 'missing',
                value: $appUrl ?: 'Не е зададено',
                help: 'Сменете APP_URL с реалния домейн и попълнете PRIMARY_DOMAIN, CANONICAL_HOST и REDIRECT_HOSTS.',
            );
        }

        if (blank($primaryDomain) || blank($canonicalHost)) {
            return self::makeCheck(
                label: 'Домейн и APP_URL',
                status: 'warning',
                value: $appUrl,
                help: 'APP_URL е зададен, но PRIMARY_DOMAIN или CANONICAL_HOST още липсва.',
            );
        }

        return self::makeCheck(
            label: 'Домейн и APP_URL',
            status: 'ready',
            value: $appUrl,
            help: 'Публичният адрес и каноничният домейн са подготвени.',
        );
    }

    private static function domainRedirectCheck(): array
    {
        $canonicalHost = (string) config('communications.domain.canonical_host');
        $redirectHosts = collect(config('communications.domain.redirect_hosts', []))
            ->filter(fn (mixed $host): bool => filled($host))
            ->values()
            ->all();

        if (blank($canonicalHost)) {
            return self::makeCheck(
                label: 'Redirect между домейните',
                status: 'missing',
                value: 'CANONICAL_HOST липсва',
                help: 'Задайте основния домейн, към който да сочат всички пренасочвания.',
            );
        }

        if ($redirectHosts === []) {
            return self::makeCheck(
                label: 'Redirect между домейните',
                status: 'warning',
                value: 'Няма алтернативни домейни за redirect',
                help: 'Ако ползвате втори домейн като jarbl.bg, добавете го в REDIRECT_HOSTS, за да се пренасочва към каноничния адрес.',
            );
        }

        return self::makeCheck(
            label: 'Redirect между домейните',
            status: 'ready',
            value: implode(', ', $redirectHosts).' → '.$canonicalHost,
            help: 'Алтернативните домейни ще се пренасочват към основния адрес.',
        );
    }

    private static function httpsCheck(): array
    {
        $forceHttps = (bool) config('communications.domain.force_https');

        if (! $forceHttps) {
            return self::makeCheck(
                label: 'HTTPS и каноничен redirect',
                status: 'warning',
                value: 'FORCE_HTTPS=false',
                help: 'В production включете FORCE_HTTPS=true и активирайте SSL сертификат.',
            );
        }

        return self::makeCheck(
            label: 'HTTPS и каноничен redirect',
            status: 'ready',
            value: 'FORCE_HTTPS=true',
            help: 'Приложението е настроено да форсира HTTPS URL-и.',
        );
    }

    private static function databaseCheck(): array
    {
        $database = (string) config('database.default');

        if ($database === 'sqlite') {
            return self::makeCheck(
                label: 'Production база данни',
                status: 'warning',
                value: 'sqlite',
                help: 'За production преминете към MySQL или PostgreSQL, вместо локален sqlite файл.',
            );
        }

        return self::makeCheck(
            label: 'Production база данни',
            status: 'ready',
            value: $database,
            help: 'Избрана е сървърна база данни, подходяща за production.',
        );
    }

    private static function adminAccountCheck(): array
    {
        if (! Schema::hasTable('users')) {
            return self::makeCheck(
                label: 'Админ достъп',
                status: 'missing',
                value: 'Таблицата users липсва',
                help: 'Пуснете миграциите, преди да създавате админ профил.',
            );
        }

        $adminCount = User::query()->where('role', 'admin')->count();

        if ($adminCount > 0) {
            return self::makeCheck(
                label: 'Админ достъп',
                status: 'ready',
                value: "{$adminCount} admin профил(а)",
                help: 'Има поне един администраторски профил за управление на сайта.',
            );
        }

        if (filled(env('ADMIN_USER_EMAIL')) && filled(env('ADMIN_USER_PASSWORD'))) {
            return self::makeCheck(
                label: 'Админ достъп',
                status: 'warning',
                value: (string) env('ADMIN_USER_EMAIL'),
                help: 'Admin профилът е подготвен в .env. Изпълнете db seed, за да бъде създаден в production базата.',
            );
        }

        return self::makeCheck(
            label: 'Админ достъп',
            status: 'missing',
            value: 'Няма admin профил',
            help: 'Задайте ADMIN_USER_EMAIL и ADMIN_USER_PASSWORD и изпълнете AdminUserSeeder преди launch.',
        );
    }

    private static function mailCheck(): array
    {
        $mailer = (string) config('mail.default');
        $fromAddress = (string) config('mail.from.address');

        if (in_array($mailer, ['array', 'log'], true)) {
            return self::makeCheck(
                label: 'Email доставка',
                status: 'missing',
                value: "MAIL_MAILER={$mailer}",
                help: 'Сменете mail driver-а с реален доставчик като Resend или SMTP.',
            );
        }

        if ($mailer === 'resend' && blank(config('services.resend.key'))) {
            return self::makeCheck(
                label: 'Email доставка',
                status: 'missing',
                value: 'Resend без API ключ',
                help: 'Попълнете RESEND_API_KEY преди launch.',
            );
        }

        if ($mailer === 'smtp' && (
            blank(config('mail.mailers.smtp.host'))
            || blank(config('mail.mailers.smtp.username'))
            || blank(config('mail.mailers.smtp.password'))
            || blank($fromAddress)
        )) {
            return self::makeCheck(
                label: 'Email доставка',
                status: 'missing',
                value: 'SMTP конфигурацията е непълна',
                help: 'Попълнете SMTP host, username, password и MAIL_FROM_ADDRESS.',
            );
        }

        return self::makeCheck(
            label: 'Email доставка',
            status: 'ready',
            value: "{$mailer} | {$fromAddress}",
            help: 'Имейл каналът има production mailer и подател.',
        );
    }

    private static function queueCheck(): array
    {
        $queue = (string) config('queue.default');

        if ($queue === 'sync') {
            return self::makeCheck(
                label: 'Queue worker',
                status: 'warning',
                value: 'QUEUE_CONNECTION=sync',
                help: 'За launch е по-добре да ползвате database/redis queue и отделен worker процес.',
            );
        }

        return self::makeCheck(
            label: 'Queue worker',
            status: 'ready',
            value: $queue,
            help: 'Асинхронните задачи могат да се изпълняват извън уеб заявката.',
        );
    }

    private static function sessionCheck(): array
    {
        $secureCookie = (bool) config('session.secure');

        if (! $secureCookie) {
            return self::makeCheck(
                label: 'Сигурни session cookies',
                status: 'warning',
                value: 'SESSION_SECURE_COOKIE=false',
                help: 'В production включете secure cookies, за да работят само през HTTPS.',
            );
        }

        return self::makeCheck(
            label: 'Сигурни session cookies',
            status: 'ready',
            value: 'SESSION_SECURE_COOKIE=true',
            help: 'Потребителските сесии са ограничени до сигурни HTTPS заявки.',
        );
    }

    private static function makeCheck(string $label, string $status, string $value, string $help): array
    {
        return compact('label', 'status', 'value', 'help');
    }
}
