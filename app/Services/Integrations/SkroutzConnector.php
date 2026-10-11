<?php

namespace App\Services\Integrations;

use App\Models\ExternalDocument;
use App\Models\IntegrationConnection;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Validator;

class SkroutzConnector
{
    private const API = 'https://api.skroutz.gr/merchants';

    public function __construct(private readonly ApiClient $api, private readonly SkroutzCatalog $catalog) {}

    public function sync(IntegrationConnection $connection): int
    {
        $orders = ExternalDocument::where('provider', 'skroutz')->where('kind', 'orders')->where('created_at', '>=', now()->subDays(30))->pluck('external_id');
        foreach ($orders as $code) {
            $this->order($connection, $code);
        }

        $count = $orders->count();
        $mode = $connection->settings['products_mode'] ?? 'off';
        if ($mode === 'validation') {
            return $count + $this->validateCatalog($connection);
        }
        if ($mode === 'live') {
            return $count + $this->syncCatalog($connection);
        }

        return $count;
    }

    public function order(IntegrationConnection $connection, string $code): ExternalDocument
    {
        if (! preg_match('/^[0-9]{6}-[0-9]+$/', $code)) {
            throw new IntegrationException('Невалиден код на поръчка от Skroutz.');
        }
        $data = $this->api->json($this->request($this->ordersToken($connection))
            ->get(self::API.'/ecommerce/orders/'.$code));
        $validator = Validator::make($data, ['order.code' => 'required|string', 'order.state' => 'required|string|max:100', 'order.line_items' => 'present|array']);
        if ($validator->fails() || $data['order']['code'] !== $code) {
            throw new IntegrationException('Skroutz върна непълна или различна поръчка.');
        }

        // Persist only the authenticated API response, never the unverified webhook payload.
        return ExternalDocument::updateOrCreate(['provider' => 'skroutz', 'kind' => 'orders', 'external_id' => $code], [
            'reference' => $code, 'status' => $data['order']['state'], 'currency_code' => 'EUR',
            'payload' => $data['order'], 'synced_at' => now(),
        ]);
    }

    public function validateCatalog(IntegrationConnection $connection): int
    {
        $products = $this->catalog->batch()['data'];
        foreach (array_chunk($products, 500) as $chunk) {
            $data = $this->api->json($this->request($this->productsToken($connection))
                ->post(self::API.'/products/batch/validations', ['data' => $chunk]));
            if (($data['success'] ?? false) !== true) {
                throw new IntegrationException('Skroutz не потвърди продуктовата партида. Данните не са изпратени за публикуване.');
            }
        }

        return count($products);
    }

    public function syncCatalog(IntegrationConnection $connection): int
    {
        if ($this->pollPendingBatches($connection)) {
            return 0;
        }

        $products = $this->catalog->batch()['data'];
        $pending = [];
        foreach (array_chunk($products, 500) as $chunk) {
            $data = $this->api->json($this->request($this->productsToken($connection))
                ->post(self::API.'/products/batch', ['data' => $chunk]));
            if (($data['success'] ?? false) !== true || ! is_string($data['id'] ?? null) || ! preg_match('/^[A-Za-z0-9_-]{6,100}$/', $data['id'])) {
                throw new IntegrationException('Skroutz не прие продуктовата партида за обновяване.');
            }
            $pending[] = ['id' => $data['id'], 'submitted_at' => now()->toIso8601String()];
        }
        $state = $connection->state ?? [];
        $state['product_batches'] = $pending;
        $connection->update(['state' => $state]);

        return count($products);
    }

    private function pollPendingBatches(IntegrationConnection $connection): bool
    {
        $state = $connection->state ?? [];
        $pending = $state['product_batches'] ?? [];
        if ($pending === []) {
            return false;
        }

        $remaining = [];
        $failures = [];
        foreach ($pending as $batch) {
            $id = is_array($batch) ? ($batch['id'] ?? '') : '';
            if (! is_string($id) || ! preg_match('/^[A-Za-z0-9_-]{6,100}$/', $id)) {
                continue;
            }
            $data = $this->api->json($this->request($this->productsToken($connection))
                ->get(self::API.'/products/batch/'.$id.'/status'));
            if (($data['status'] ?? null) === 'in_progress') {
                $remaining[] = $batch;

                continue;
            }
            if (($data['status'] ?? null) !== 'completed') {
                throw new IntegrationException('Skroutz върна невалиден статус за продуктова партида.');
            }
            foreach ($data['results'] ?? [] as $result) {
                if (($result['status'] ?? null) === 'failure') {
                    $failures[] = (string) ($result['product_id'] ?? 'неизвестен продукт');
                }
            }
        }
        $state['product_batches'] = $remaining;
        $connection->update(['state' => $state]);
        if ($failures !== []) {
            throw new IntegrationException('Skroutz отказа обновяване за: '.implode(', ', array_slice($failures, 0, 10)).'.');
        }

        return $remaining !== [];
    }

    private function request(string $token): PendingRequest
    {
        return $this->api->request()->withToken($token)
            ->withHeaders(['Accept' => 'application/vnd.skroutz+json; version=3.0']);
    }

    private function ordersToken(IntegrationConnection $connection): string
    {
        $token = $connection->credentials['token'] ?? null;
        if (! is_string($token) || $token === '') {
            throw new IntegrationException('Липсва Skroutz Orders API token.');
        }

        return $token;
    }

    private function productsToken(IntegrationConnection $connection): string
    {
        $token = $connection->credentials['products_token'] ?? null;
        if (! is_string($token) || $token === '') {
            throw new IntegrationException('Липсва Skroutz Products API token.');
        }

        return $token;
    }
}
