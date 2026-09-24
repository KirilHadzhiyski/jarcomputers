<?php

namespace App\Services\Integrations;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ApiClient
{
    public function request(): PendingRequest
    {
        return Http::acceptJson()->withOptions(['verify' => config('integrations.ca_bundle') ?: true])->connectTimeout(10)->timeout(config('integrations.http_timeout', 30))
            ->retry(2, 500, throw: false)->withoutRedirecting();
    }

    public function json(Response $response): array
    {
        $this->check($response);
        $data = $response->json();
        if (! is_array($data) || ($data['status'] ?? null) === false || ! empty($data['is_error'])) {
            throw new IntegrationException('API върна невалиден отговор или отказа заявката. Проверете достъпа и формата.');
        }

        return $data;
    }

    public function check(Response $response): void
    {
        if (! $response->successful()) {
            throw new IntegrationException(match ($response->status()) {
                401, 403 => 'Отказан достъп. Проверете акаунта, ключовете и разрешенията.',
                429 => 'Достигнат лимит на API. Следващият опит ще бъде по график.',
                default => 'API не е достъпно (HTTP '.$response->status().'). Последните получени данни са запазени.',
            });
        }
    }

    public function googleToken(array $credentials): string
    {
        $data = $this->json($this->request()->asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $credentials['client_id'], 'client_secret' => $credentials['client_secret'],
            'refresh_token' => $credentials['refresh_token'], 'grant_type' => 'refresh_token',
        ]));
        if (empty($data['access_token']) || ! is_string($data['access_token'])) {
            throw new IntegrationException('Google не върна валиден access token. Свържете акаунта отново.');
        }

        return $data['access_token'];
    }
}
