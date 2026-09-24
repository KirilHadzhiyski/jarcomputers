<?php

namespace App\Services\Integrations;

use App\Models\ExternalDocument;
use App\Models\IntegrationConnection;
use App\Models\SupplierProduct;
use Illuminate\Support\Carbon;

class JarConnector
{
    public function __construct(private readonly ApiClient $api) {}

    public function sync(IntegrationConnection $connection): int
    {
        $state = $connection->state ?? [];
        $full = empty($state['catalog_at']) || Carbon::parse($state['catalog_at'])->lt(now()->subDay());
        $credentials = $connection->credentials;
        $currency = $connection->settings['currency'];
        $records = 0;
        $page = 1;
        do {
            $data = $this->get($credentials, $full ? 'products' : 'products_short', ['page' => $page]);
            if (! isset($data['products']) || ! is_array($data['products']) || ! is_numeric(data_get($data, 'pages.cnt'))) {
                throw new IntegrationException('JAR върна непълен продуктов отговор. Проверете достъпа до каталога.');
            }
            $pages = (int) data_get($data, 'pages.cnt');
            if ($pages > config('integrations.max_pages') || $pages < 0) {
                throw new IntegrationException('JAR върна неочакван брой страници. Синхронизацията е прекъсната.');
            }
            foreach ($data['products'] as $product) {
                if (! is_array($product) || empty($product['code'])) {
                    throw new IntegrationException('Продукт от JAR няма код. Импортът не е завършен.');
                }
                $attributes = ['currency_code' => $currency, 'synced_at' => now()];
                foreach (['price' => 'retail_price', 'dealer_price' => 'dealer_price'] as $source => $target) {
                    if (array_key_exists($source, $product)) {
                        if ($product[$source] !== null && (! is_numeric($product[$source]) || $product[$source] < 0)) {
                            throw new IntegrationException('JAR върна невалидна цена. Импортът не е завършен.');
                        }
                        $attributes[$target] = $product[$source];
                    }
                }
                foreach (['product_name' => 'name', 'brand_name' => 'brand', 'category_path' => 'category', 'avail' => 'availability', 'warranty' => 'warranty_months', 'main_image' => 'image_url'] as $source => $target) {
                    if (array_key_exists($source, $product)) {
                        $attributes[$target] = $product[$source];
                    }
                }
                SupplierProduct::updateOrCreate(['provider' => 'jar', 'code' => $product['code']], $attributes);
                $records++;
            }
            $page++;
        } while ($page <= $pages);

        // Always revisit the window: an old order or invoice may have a new status.
        $period = ['from' => now()->subDays(30)->toDateString(), 'to' => now()->toDateString()];
        foreach (['orders', 'documents', 'invoices'] as $kind) {
            $data = $this->get($credentials, $kind, $period);
            if (! array_key_exists('item', $data) || ! is_array($data['item'])) {
                throw new IntegrationException('JAR върна непълен списък с документи. Импортът не е завършен.');
            }
            $items = $data['item'];
            if (isset($items['pid']) || isset($items['id'])) {
                $items = [$items];
            }
            foreach ($items as $item) {
                $id = is_array($item) ? ($item['id'] ?? $item['pid'] ?? null) : null;
                if (! is_array($item) || blank($id) || ! is_scalar($id)) {
                    throw new IntegrationException('Документ от JAR няма идентификатор. Импортът не е завършен.');
                }
                ExternalDocument::updateOrCreate(['provider' => 'jar', 'kind' => $kind, 'external_id' => (string) $id], [
                    'reference' => $item['reference'] ?? null, 'status' => $item['status'] ?? $item['document_type'] ?? null,
                    'total' => isset($item['total']) && is_numeric($item['total']) ? $item['total'] : null,
                    'currency_code' => $currency, 'document_date' => $item['edate'] ?? null,
                    'payload' => $item, 'synced_at' => now(),
                ]);
                $records++;
            }
        }
        if ($full) {
            $state['catalog_at'] = now()->toIso8601String();
        }
        $connection->update(['state' => $state]);

        return $records;
    }

    private function get(array $credentials, string $method, array $query = []): array
    {
        // HTTPS works on the official host. Never follow an HTTP redirect with credentials.
        return $this->api->json($this->api->request()->withBasicAuth($credentials['username'], $credentials['password'])
            ->get('https://api.jarcomputers.com/api/'.$method, ['format' => 'json', ...$query]));
    }
}
