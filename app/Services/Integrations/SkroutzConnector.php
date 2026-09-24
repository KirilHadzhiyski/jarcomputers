<?php

namespace App\Services\Integrations;

use App\Models\ExternalDocument;
use App\Models\IntegrationConnection;
use Illuminate\Support\Facades\Validator;

class SkroutzConnector
{
    public function __construct(private readonly ApiClient $api) {}

    public function sync(IntegrationConnection $connection): int
    {
        $orders = ExternalDocument::where('provider', 'skroutz')->where('kind', 'orders')->where('created_at', '>=', now()->subDays(30))->pluck('external_id');
        foreach ($orders as $code) {
            $this->order($connection, $code);
        }

        return $orders->count();
    }

    public function order(IntegrationConnection $connection, string $code): ExternalDocument
    {
        if (! preg_match('/^[0-9]{6}-[0-9]+$/', $code)) {
            throw new IntegrationException('Невалиден код на поръчка от Skroutz.');
        }
        $data = $this->api->json($this->api->request()->withToken($connection->credentials['token'])
            ->withHeaders(['Accept' => 'application/vnd.skroutz+json; version=3.0'])
            ->get('https://api.skroutz.gr/merchants/ecommerce/orders/'.$code));
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
}
